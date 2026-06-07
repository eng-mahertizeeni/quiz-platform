<?php

namespace App\Services;

use App\Helpers\ArabicHelper;
use App\Models\BluffGame;
use App\Models\BluffPlayer;
use App\Models\BluffQuestion;
use App\Models\BluffRound;
use App\Models\BluffRoundAnswer;
use App\Models\BluffVote;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BluffService
{
    public function createGame(int $userId, int $totalRounds = 8, array $selectedCategories = [], int $questionDuration = 30): BluffGame
    {
        if (empty($selectedCategories)) {
            throw new \InvalidArgumentException('يجب اختيار فقرة واحدة على الأقل');
        }

        $allowed = [15, 20, 25, 30, 35, 40];
        if (!in_array($questionDuration, $allowed)) {
            $questionDuration = 30;
        }

        return DB::transaction(function () use ($userId, $totalRounds, $selectedCategories, $questionDuration) {
            $game = BluffGame::create([
                'bluff_creator_id' => $userId,
                'total_rounds' => $totalRounds,
                'question_duration' => $questionDuration,
                'selected_categories' => $selectedCategories,
            ]);

            BluffPlayer::create([
                'bluff_game_id' => $game->id,
                'user_id' => $userId,
            ]);

            return $game->fresh();
        });
    }

    public function joinGame(string $code, int $userId, ?string $displayName = null): ?BluffGame
    {
        $game = BluffGame::where('code', $code)
            ->where('status', 'waiting')
            ->firstOrFail();

        $exists = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            BluffPlayer::create([
                'bluff_game_id' => $game->id,
                'user_id' => $userId,
                'display_name' => $displayName ?: null,
            ]);
        }

        return $game->fresh();
    }

    public function startGame(BluffGame $game): void
    {
        DB::transaction(function () use ($game) {
            if (empty($game->selected_categories)) {
                throw new \RuntimeException('لم يتم اختيار أي فقرة');
            }

            // Verify each selected category has at least one question
            foreach ($game->selected_categories as $catId) {
                $count = BluffQuestion::where('category_id', $catId)->count();
                if ($count === 0) {
                    $cat = Category::find($catId);
                    $name = $cat?->name ?? $catId;
                    throw new \RuntimeException("لا توجد أسئلة في فقرة \"{$name}\"");
                }
            }

            $players = $game->players()->orderBy('id')->get();
            $numPlayers = $players->count();

            $now = now();
            $rounds = [];
            for ($i = 1; $i <= $game->total_rounds; $i++) {
                $selector = $players[($i - 1) % $numPlayers];
                $rounds[] = [
                    'bluff_game_id' => $game->id,
                    'bluff_question_id' => null,
                    'selected_by_player_id' => $selector->id,
                    'round_number' => $i,
                    'status' => 'selecting',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            BluffRound::insert($rounds);

            $game->update([
                'status' => 'playing',
                'current_round' => 1,
                'started_at' => $now,
            ]);
        });
    }

    public function selectCategory(BluffGame $game, BluffRound $round, BluffPlayer $player, int $categoryId): array
    {
        if ($game->status !== 'playing') {
            return ['status' => 'error', 'message' => 'اللعبة لم تبدأ بعد'];
        }
        if ($round->status !== 'selecting') {
            return ['status' => 'error', 'message' => 'تم اختيار الفقرة بالفعل'];
        }
        if ($round->selected_by_player_id !== $player->id) {
            return ['status' => 'error', 'message' => 'ليس دورك لاختيار الفقرة'];
        }

        $selectedCats = $game->selected_categories ?? [];
        if (!in_array($categoryId, $selectedCats)) {
            return ['status' => 'error', 'message' => 'فقرة غير صالحة'];
        }

        $usedIds = BluffRound::where('bluff_game_id', $game->id)
            ->whereNotNull('bluff_question_id')
            ->pluck('bluff_question_id')
            ->toArray();

        $question = BluffQuestion::where('category_id', $categoryId)
            ->whereNotIn('id', $usedIds)
            ->inRandomOrder()
            ->first();

        if (!$question) {
            return ['status' => 'error', 'message' => 'لا يوجد أسئلة كافية في هذه الفقرة'];
        }

        $round->update([
            'bluff_question_id' => $question->id,
            'category_id' => $categoryId,
            'status' => 'answering',
            'answering_started_at' => now(),
        ]);

        return ['status' => 'ok'];
    }

    public function getGameState(BluffGame $game): array
    {
        $authUserId = Auth::id();

        $game->load('players.user');

        $currentRound = BluffRound::where('bluff_game_id', $game->id)
            ->where('round_number', $game->current_round)
            ->with('question')
            ->first();

        $authPlayer = $game->players->firstWhere('user_id', $authUserId);

        $players = $game->players->map(fn($p) => [
            'id' => $p->id,
            'user_id' => $p->user_id,
            'name' => $p->display_name,
            'avatar' => $p->user->avatar_url,
            'total_score' => $p->total_score,
        ])->values()->toArray();

        $scores = collect($players)
            ->map(fn($p) => [
                'player_name' => $p['name'],
                'player_avatar' => $p['avatar'],
                'total_score' => $p['total_score'],
            ])
            ->sortByDesc('total_score')
            ->values()
            ->toArray();

        $roundData = null;
        if ($currentRound) {
            if ($currentRound->status === 'selecting') {
                $currentRound->load('selector');

                $usedQIds = BluffRound::where('bluff_game_id', $game->id)
                    ->whereNotNull('bluff_question_id')
                    ->pluck('bluff_question_id')
                    ->toArray();

                $categories = [];
                foreach (($game->selected_categories ?? []) as $catId) {
                    $remaining = BluffQuestion::where('category_id', $catId)
                        ->whereNotIn('id', $usedQIds)
                        ->count();
                    if ($remaining > 0) {
                        $cat = Category::find($catId);
                        if ($cat) {
                            $categories[] = [
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'remaining_questions' => $remaining,
                            ];
                        }
                    }
                }

                $roundData = [
                    'id' => $currentRound->id,
                    'round_number' => $currentRound->round_number,
                    'status' => 'selecting',
                    'is_selector' => $authPlayer && $currentRound->selected_by_player_id === $authPlayer->id,
                    'selector_name' => $currentRound->selector?->display_name ?? '—',
                    'available_categories' => $categories,
                ];
            } else {
                if ($currentRound->status === 'finished') {
                    $currentRound->load(['answers.player.user', 'answers.votes.voter.user']);
                } elseif ($currentRound->status === 'voting') {
                    $currentRound->load(['answers', 'votes', 'answers.votes']);
                } elseif ($currentRound->status === 'answering') {
                    $currentRound->load('answers');
                }

                $allAnswers = $currentRound->answers ?? collect();
                $playerIds = $game->players->pluck('id');
                $answeredIds = $allAnswers->whereNotNull('bluff_player_id')->pluck('bluff_player_id');
                $voterIds = $currentRound->votes?->pluck('bluff_voter_id')
                    ?? $allAnswers->flatMap(fn($a) => $a->votes?->pluck('bluff_voter_id') ?? collect())
                    ?? collect();

                $correctAnswer = $currentRound->question?->correct_answer ?? '';
                $wordCount = $correctAnswer !== ''
                    ? count(preg_split('/\s+/u', trim($correctAnswer)))
                    : 0;
                $isNumeric = $correctAnswer !== '' && \App\Helpers\ArabicHelper::isNumeric($correctAnswer);

                $answerTimeExpired = false;
                $voteTimeExpired = false;
                $answerRemaining = 0;
                $voteRemaining = 0;

                if ($currentRound->status === 'answering' && $currentRound->answering_started_at) {
                    $answerEnd = $currentRound->answering_started_at->copy()->addSeconds($game->question_duration);
                    $answerTimeExpired = $answerEnd->isPast();
                    $answerRemaining = $answerTimeExpired ? 0 : (int) ceil(max(0, now()->diffInSeconds($answerEnd, false)));
                } elseif ($currentRound->status === 'voting' && $currentRound->voting_started_at) {
                    $voteEnd = $currentRound->voting_started_at->copy()->addSeconds($game->question_duration);
                    $voteTimeExpired = $voteEnd->isPast();
                    $voteRemaining = $voteTimeExpired ? 0 : (int) ceil(max(0, now()->diffInSeconds($voteEnd, false)));
                }

                $roundData = [
                    'id' => $currentRound->id,
                    'round_number' => $currentRound->round_number,
                    'status' => $currentRound->status,
                    'question_text' => $currentRound->question?->question_text ?? '',
                    'correct_answer_word_count' => $wordCount,
                    'correct_answer_is_numeric' => $isNumeric,
                    'total_players' => $game->players->count(),
                    'answers_submitted' => $allAnswers->where('is_real_fake', false)->count(),
                    'votes_cast' => $voterIds->count(),
                    'waiting_for_answers' => $playerIds->reject(fn($id) => $answeredIds->contains($id))->count(),
                    'answer_time_expired' => $answerTimeExpired,
                    'vote_time_expired' => $voteTimeExpired,
                    'answer_remaining' => $answerRemaining,
                    'vote_remaining' => $voteRemaining,
                ];

                $playerAnswer = $allAnswers->firstWhere('bluff_player_id', $authPlayer?->id);
                $roundData['my_answer_id'] = $playerAnswer?->id;
                $roundData['has_answered'] = $authPlayer && $answeredIds->contains($authPlayer->id);

                if ($currentRound->status === 'voting') {
                    $realAnswer = $allAnswers->firstWhere('is_real_fake', true);
                    $fakeAnswers = $allAnswers->where('is_real_fake', false);

                    $grouped = $fakeAnswers->groupBy(fn($a) => $a->answer_text);
                    $displayAnswers = $grouped->map(function ($group) {
                        $first = $group->first();
                        return [
                            'id' => $first->id,
                            'answer_text' => $first->answer_text,
                            'playercount' => $group->count(),
                        ];
                    });

                    if ($realAnswer) {
                        $displayAnswers->push([
                            'id' => $realAnswer->id,
                            'answer_text' => $realAnswer->answer_text,
                            'playercount' => 0,
                        ]);
                    }

                    $roundData['display_answers'] = $displayAnswers->sortBy('id')->values();
                    $roundData['has_voted'] = $authPlayer && $voterIds->contains($authPlayer->id);
                }

                if ($currentRound->status === 'finished') {
                    $realAnswer = $allAnswers->firstWhere('is_real_fake', true);
                    $fakeAnswers = $allAnswers->where('is_real_fake', false);

                    $results = collect();
                    $grouped = $fakeAnswers->groupBy(fn($a) => $a->answer_text);
                    foreach ($grouped as $text => $group) {
                        $totalVotes = $group->sum(fn($a) => $a->votes->count());
                        $authors = $group->pluck('player.display_name')->filter();
                        $voters = $group->flatMap(fn($a) => $a->votes->map(fn($v) => $v->voter?->display_name ?? '—'))->filter();
                        $results->push([
                            'answer_text' => $text,
                            'is_real_fake' => false,
                            'authors' => $authors->values(),
                            'author_count' => $group->count(),
                            'vote_count' => $totalVotes,
                            'voters' => $voters->values(),
                        ]);
                    }

                    if ($realAnswer) {
                        $results->push([
                            'answer_text' => $realAnswer->answer_text,
                            'is_real_fake' => true,
                            'author_name' => 'النظام',
                            'vote_count' => $realAnswer->votes->count(),
                            'voters' => $realAnswer->votes->map(fn($v) => $v->voter?->display_name ?? '—')->filter()->values(),
                            'each_point' => 2,
                        ]);
                    }

                    $roundData['results'] = $results->sortBy('id')->values();
                    $roundData['correct_answer_text'] = $currentRound->question?->correct_answer ?? '';
                    $roundData['all_voted'] = $voterIds->count() >= $game->players->count();
                }
            }
        }

        return [
            'id' => $game->id,
            'code' => $game->code,
            'status' => $game->status,
            'current_round' => $game->current_round,
            'total_rounds' => $game->total_rounds,
            'question_duration' => $game->question_duration,
            'players' => $players,
            'current_round_data' => $roundData,
            'scores' => $scores,
            'is_creator' => $game->bluff_creator_id === $authUserId,
            'is_spectator' => !$authPlayer,
        ];
    }

    public function submitAnswer(BluffGame $game, BluffRound $round, BluffPlayer $player, string $answerText): array
    {
        $already = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('bluff_player_id', $player->id)
            ->exists();

        if ($already) {
            return ['status' => 'error', 'message' => 'لقد قدمت إجابتك بالفعل'];
        }

        $trimmed = trim($answerText);
        if ($trimmed === '' || mb_strlen($trimmed) < 2) {
            return ['status' => 'error', 'message' => 'الإجابة يجب أن تكون على الأقل حرفين'];
        }

        if (!$round->question) {
            return ['status' => 'error', 'message' => 'لم يتم تحميل السؤال بعد'];
        }

        $correctAnswer = $round->question->correct_answer;
        if (ArabicHelper::matches($trimmed, $correctAnswer)) {
            return [
                'status' => 'correct_answer_rejected',
                'message' => 'إجابتك قريبة جداً من الإجابة الصحيحة أو تطابقها. اكتب إجابة مختلفة تماماً لخداع اللاعبين.',
            ];
        }

        BluffRoundAnswer::create([
            'bluff_round_id' => $round->id,
            'bluff_player_id' => $player->id,
            'answer_text' => $trimmed,
            'is_real_fake' => false,
        ]);

        $this->checkAnswersComplete($round, $game);

        return ['status' => 'ok', 'message' => 'تم حفظ إجابتك'];
    }

    public function submitVote(BluffGame $game, BluffRound $round, BluffPlayer $voter, int $votedAnswerId): array
    {
        $already = BluffVote::where('bluff_round_id', $round->id)
            ->where('bluff_voter_id', $voter->id)
            ->exists();

        if ($already) {
            return ['status' => 'error', 'message' => 'لقد صوت بالفعل'];
        }

        $answer = BluffRoundAnswer::findOrFail($votedAnswerId);

        $voterSameText = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('answer_text', $answer->answer_text)
            ->where('bluff_player_id', $voter->id)
            ->exists();

        if ($voterSameText) {
            return ['status' => 'error', 'message' => 'لا يمكنك التصويت على إجابتك الخاصة'];
        }

        BluffVote::create([
            'bluff_round_id' => $round->id,
            'bluff_voter_id' => $voter->id,
            'bluff_voted_answer_id' => $votedAnswerId,
        ]);

        $allVoted = $this->checkVotesComplete($round, $game);

        return ['status' => 'ok', 'message' => 'تم تسجيل صوتك', 'round_complete' => $allVoted];
    }

    public function advanceRound(BluffGame $game): bool
    {
        $nextRound = $game->current_round + 1;

        if ($nextRound > $game->total_rounds) {
            $game->update(['status' => 'finished', 'finished_at' => now()]);
            return false;
        }

        $game->update(['current_round' => $nextRound]);

        $nextStatus = empty($game->selected_categories) ? 'answering' : 'selecting';

        BluffRound::where('bluff_game_id', $game->id)
            ->where('round_number', $nextRound)
            ->update(['status' => $nextStatus]);

        return true;
    }

    private function checkAnswersComplete(BluffRound $round, BluffGame $game): void
    {
        $answerCount = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->whereNotNull('bluff_player_id')
            ->count();

        if ($answerCount >= $game->players()->count()) {
            if (!$round->question) return;

            BluffRoundAnswer::create([
                'bluff_round_id' => $round->id,
                'bluff_player_id' => null,
                'answer_text' => $round->question->correct_answer,
                'is_real_fake' => true,
            ]);

            $round->update(['status' => 'voting', 'voting_started_at' => now()]);
        }
    }

    private function checkVotesComplete(BluffRound $round, BluffGame $game): bool
    {
        $voteCount = $round->votes()->count();

        if ($voteCount >= $game->players()->count()) {
            $this->calculateRoundScores($round, $game);
            return true;
        }

        return false;
    }

    private function calculateRoundScores(BluffRound $round, BluffGame $game): void
    {
        $round->load('answers.votes');

        // Start with ALL game players to prevent any player from being missed
        $playerIds = $game->players()->pluck('bluff_players.id');

        // Also include any voters or answer authors not already in the game players
        foreach ($round->answers as $answer) {
            foreach ($answer->votes as $vote) {
                $playerIds->push($vote->bluff_voter_id);
            }
            if (!$answer->is_real_fake && $answer->bluff_player_id) {
                $playerIds->push($answer->bluff_player_id);
            }
        }

        $players = BluffPlayer::whereIn('id', $playerIds->unique())->get()->keyBy('id');

        $realAnswer = $round->answers->firstWhere('is_real_fake', true);
        $fakeAnswers = $round->answers->where('is_real_fake', false);

        // Correct answer detection: voter gets +2 regardless which copy they voted on
        if ($realAnswer) {
            $realAnswerText = $realAnswer->answer_text;
            foreach ($round->answers as $answer) {
                foreach ($answer->votes as $vote) {
                    if ($answer->answer_text === $realAnswerText) {
                        $player = $players->get($vote->bluff_voter_id);
                        if ($player) {
                            $player->increment('total_score', 2);
                        }
                    }
                }
            }
        }

        // Fake answers: group by text, each vote on a text gives 1 point to EACH author of that text
        $groupedByText = $fakeAnswers->groupBy(fn($a) => $a->answer_text);
        foreach ($groupedByText as $text => $group) {
            $totalVotes = $group->sum(fn($a) => $a->votes->count());
            if ($totalVotes === 0) continue;

            $authors = $players->whereIn('id', $group->pluck('bluff_player_id'));
            foreach ($authors as $author) {
                $author->increment('total_score', $totalVotes);
            }
        }

        $round->update(['status' => 'finished']);
    }

    public function getFinalResults(BluffGame $game): array
    {
        $game->load('players.user');

        $results = $game->players->map(fn($p) => [
            'player_name' => $p->display_name,
            'player_avatar' => $p->user->avatar_url,
            'total_score' => $p->total_score,
        ])->sortByDesc('total_score')->values()->toArray();

        return [
            'game' => $game,
            'results' => $results,
            'winner' => $results[0] ?? null,
        ];
    }

    public function getRoundHistory(BluffGame $game): array
    {
        return BluffRound::where('bluff_game_id', $game->id)
            ->where('status', 'finished')
            ->with(['question', 'answers.player.user', 'answers.votes.voter.user'])
            ->orderBy('round_number')
            ->get()
            ->map(fn($round) => [
                'round_number' => $round->round_number,
                'question_text' => $round->question?->question_text ?? '',
                'correct_answer' => $round->question?->correct_answer ?? '',
                'answers' => $round->answers
                    ->groupBy(fn($a) => $a->answer_text)
                    ->map(fn($group) => [
                        'answer_text' => $group->first()->answer_text,
                        'is_real_fake' => $group->first()->is_real_fake,
                        'author_name' => $group->first()->is_real_fake
                            ? 'النظام'
                            : $group->pluck('player.display_name')->filter()->implode('، '),
                        'author_count' => $group->count(),
                        'vote_count' => $group->sum(fn($a) => $a->votes->count()),
                        'voters' => $group->flatMap(fn($a) => $a->votes->map(fn($v) => $v->voter?->display_name ?? '—'))->filter()->values(),
                    ])->values(),
            ]);
    }
}
