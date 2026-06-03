<?php

namespace App\Services;

use App\Models\BluffGame;
use App\Models\BluffPlayer;
use App\Models\BluffQuestion;
use App\Models\BluffRound;
use App\Models\BluffRoundAnswer;
use App\Models\BluffVote;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BluffService
{
    private const STATE_CACHE_TTL = 2;

    public function createGame(int $userId, int $totalRounds = 8): BluffGame
    {
        return DB::transaction(function () use ($userId, $totalRounds) {
            $game = BluffGame::create([
                'bluff_creator_id' => $userId,
                'total_rounds' => $totalRounds,
            ]);

            BluffPlayer::create([
                'bluff_game_id' => $game->id,
                'user_id' => $userId,
            ]);

            return $game->fresh();
        });
    }

    public function joinGame(string $code, int $userId): ?BluffGame
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
            ]);
        }

        return $game->fresh();
    }

    public function startGame(BluffGame $game): void
    {
        DB::transaction(function () use ($game) {
            $questions = BluffQuestion::inRandomOrder()
                ->limit($game->total_rounds)
                ->get();

            if ($questions->count() < $game->total_rounds) {
                throw new \RuntimeException('لا يوجد عدد كافٍ من الأسئلة. تحتاج على الأقل ' . $game->total_rounds . ' سؤال.');
            }

            $now = now();
            $rounds = [];
            foreach ($questions as $i => $question) {
                $rounds[] = [
                    'bluff_game_id' => $game->id,
                    'bluff_question_id' => $question->id,
                    'round_number' => $i + 1,
                    'status' => 'answering',
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

    public function getGameState(BluffGame $game): array
    {
        $cacheKey = 'bluff_state_' . $game->id;

        return Cache::remember($cacheKey, self::STATE_CACHE_TTL, function () use ($game) {
            $authUserId = auth()->id();

            $game->load('players.user');

            $currentRound = BluffRound::where('bluff_game_id', $game->id)
                ->where('round_number', $game->current_round)
                ->with('question')
                ->first();

            $authPlayer = $game->players->firstWhere('user_id', $authUserId);

            $players = $game->players->map(fn($p) => [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'name' => $p->user->name,
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
                if ($currentRound->status === 'finished') {
                    $currentRound->load(['answers.player.user', 'answers.votes.voter.user']);
                } elseif ($currentRound->status === 'voting') {
                    $currentRound->load('answers', 'votes');
                } else {
                    $currentRound->load('answers');
                }

                $allAnswers = $currentRound->answers ?? collect();
                $playerIds = $game->players->pluck('id');
                $answeredIds = $allAnswers->whereNotNull('bluff_player_id')->pluck('bluff_player_id');
                $voterIds = $currentRound->votes?->pluck('bluff_voter_id')
                    ?? $allAnswers->flatMap(fn($a) => $a->votes?->pluck('bluff_voter_id') ?? collect())
                    ?? collect();

                $roundData = [
                    'id' => $currentRound->id,
                    'round_number' => $currentRound->round_number,
                    'status' => $currentRound->status,
                    'question_text' => $currentRound->question?->question_text ?? '',
                    'total_players' => $game->players->count(),
                    'answers_submitted' => $allAnswers->where('is_real_fake', false)->count(),
                    'votes_cast' => $voterIds->count(),
                    'waiting_for_answers' => $playerIds->reject(fn($id) => $answeredIds->contains($id))->count(),
                ];

                $playerAnswer = $allAnswers->firstWhere('bluff_player_id', $authPlayer?->id);
                $roundData['my_answer_id'] = $playerAnswer?->id;
                $roundData['has_answered'] = $authPlayer && $answeredIds->contains($authPlayer->id);

                if ($currentRound->status === 'voting') {
                    $roundData['display_answers'] = $allAnswers->shuffle()->map(fn($a) => [
                        'id' => $a->id,
                        'answer_text' => $a->answer_text,
                    ])->values();
                    $roundData['has_voted'] = $authPlayer && $voterIds->contains($authPlayer->id);
                }

                if ($currentRound->status === 'finished') {
                    $roundData['results'] = $allAnswers->map(fn($a) => [
                        'answer_text' => $a->answer_text,
                        'is_real_fake' => $a->is_real_fake,
                        'author_name' => $a->player?->user?->name ?? 'النظام',
                        'vote_count' => $a->votes->count(),
                        'voters' => $a->votes->map(fn($v) => $v->voter?->user?->name ?? '—')->filter()->values(),
                        'each_point' => $a->is_real_fake ? 2 : 1,
                    ])->shuffle()->values();
                    $roundData['correct_answer_text'] = $currentRound->question?->correct_answer ?? '';
                    $roundData['all_voted'] = $voterIds->count() >= $game->players->count();
                }
            }

            return [
                'id' => $game->id,
                'code' => $game->code,
                'status' => $game->status,
                'current_round' => $game->current_round,
                'total_rounds' => $game->total_rounds,
                'players' => $players,
                'current_round_data' => $roundData,
                'scores' => $scores,
                'is_creator' => $game->bluff_creator_id === $authUserId,
                'is_spectator' => !$authPlayer,
            ];
        });
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

        $correctAnswer = $round->question->correct_answer;
        if (mb_strtolower($trimmed) === mb_strtolower(trim($correctAnswer))) {
            return [
                'status' => 'correct_answer_rejected',
                'message' => 'مبروك، عرفت الإجابة الصحيحة. اكتب إجابة أخرى لخداع اللاعبين.',
            ];
        }

        BluffRoundAnswer::create([
            'bluff_round_id' => $round->id,
            'bluff_player_id' => $player->id,
            'answer_text' => $trimmed,
            'is_real_fake' => false,
        ]);

        Cache::forget('bluff_state_' . $game->id);
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

        if ($answer->bluff_player_id === $voter->id) {
            return ['status' => 'error', 'message' => 'لا يمكنك التصويت على إجابتك الخاصة'];
        }

        BluffVote::create([
            'bluff_round_id' => $round->id,
            'bluff_voter_id' => $voter->id,
            'bluff_voted_answer_id' => $votedAnswerId,
        ]);

        Cache::forget('bluff_state_' . $game->id);
        $allVoted = $this->checkVotesComplete($round, $game);

        return ['status' => 'ok', 'message' => 'تم تسجيل صوتك', 'round_complete' => $allVoted];
    }

    public function advanceRound(BluffGame $game): bool
    {
        $nextRound = $game->current_round + 1;

        if ($nextRound > $game->total_rounds) {
            $game->update(['status' => 'finished', 'finished_at' => now()]);
            Cache::forget('bluff_state_' . $game->id);
            return false;
        }

        $game->update(['current_round' => $nextRound]);

        BluffRound::where('bluff_game_id', $game->id)
            ->where('round_number', $nextRound)
            ->update(['status' => 'answering']);

        Cache::forget('bluff_state_' . $game->id);

        return true;
    }

    private function checkAnswersComplete(BluffRound $round, BluffGame $game): void
    {
        $answerCount = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->whereNotNull('bluff_player_id')
            ->count();

        if ($answerCount >= $game->players()->count()) {
            BluffRoundAnswer::create([
                'bluff_round_id' => $round->id,
                'bluff_player_id' => null,
                'answer_text' => $round->question->correct_answer,
                'is_real_fake' => true,
            ]);

            $round->update(['status' => 'voting']);

            Cache::forget('bluff_state_' . $game->id);
        }
    }

    private function checkVotesComplete(BluffRound $round, BluffGame $game): bool
    {
        $voteCount = $round->votes()->count();

        if ($voteCount >= $game->players()->count()) {
            $this->calculateRoundScores($round, $game);
            Cache::forget('bluff_state_' . $game->id);
            return true;
        }

        return false;
    }

    private function calculateRoundScores(BluffRound $round, BluffGame $game): void
    {
        $round->load('answers.votes');

        $playerIds = collect();
        foreach ($round->answers as $answer) {
            foreach ($answer->votes as $vote) {
                $playerIds->push($vote->bluff_voter_id);
            }
            if (!$answer->is_real_fake && $answer->bluff_player_id) {
                $playerIds->push($answer->bluff_player_id);
            }
        }

        $players = BluffPlayer::whereIn('id', $playerIds->unique())->get()->keyBy('id');

        foreach ($round->answers as $answer) {
            foreach ($answer->votes as $vote) {
                $player = $players->get($vote->bluff_voter_id);
                if (!$player) continue;
                if ($answer->is_real_fake) {
                    $player->increment('total_score', 2);
                }
            }

            if (!$answer->is_real_fake && $answer->votes->count() > 0) {
                $author = $players->get($answer->bluff_player_id);
                if ($author) {
                    $author->increment('total_score', $answer->votes->count());
                }
            }
        }

        $round->update(['status' => 'finished']);
        Cache::forget('bluff_state_' . $game->id);
    }

    public function getFinalResults(BluffGame $game): array
    {
        $game->load('players.user');

        $results = $game->players->map(fn($p) => [
            'player_name' => $p->user->name,
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
                'answers' => $round->answers->map(fn($a) => [
                    'answer_text' => $a->answer_text,
                    'is_real_fake' => $a->is_real_fake,
                    'author_name' => $a->player?->user?->name ?? 'النظام',
                    'vote_count' => $a->votes->count(),
                    'voters' => $a->votes->map(fn($v) => $v->voter?->user?->name ?? '—')->filter()->values(),
                ]),
            ]);
    }
}
