<?php

namespace App\Services;

use App\Helpers\ArabicHelper;
use App\Models\KuraiyatGame;
use App\Models\KuraiyatPlayer;
use App\Models\KuraiyatRound;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class KuraiyatService
{
    private const FOOTBALL_CATEGORY_ID = 1;

    public function createGame(int $userId): KuraiyatGame
    {
        return DB::transaction(function () use ($userId) {
            $game = KuraiyatGame::create([
                'created_by' => $userId,
                'type' => 'who_am_i',
                'total_rounds' => 5,
            ]);

            KuraiyatPlayer::create([
                'kuraiyat_game_id' => $game->id,
                'user_id' => $userId,
            ]);

            return $game->fresh();
        });
    }

    public function startGame(KuraiyatGame $game): void
    {
        DB::transaction(function () use ($game) {
            $questions = Question::where('category_id', self::FOOTBALL_CATEGORY_ID)
                ->where('status', 'active')
                ->where('difficulty', 'very_hard')
                ->whereNotNull('hint_1')
                ->whereNotNull('hint_2')
                ->whereNotNull('hint_3')
                ->whereNotNull('hint_4')
                ->whereNotNull('hint_5')
                ->inRandomOrder()
                ->limit($game->total_rounds)
                ->get();

            if ($questions->count() < $game->total_rounds) {
                throw new \RuntimeException('لا يوجد عدد كافٍ من الأسئلة. تحتاج 5 أسئلة على الأقل.');
            }

            $now = now();
            $rounds = [];
            foreach ($questions as $i => $q) {
                $rounds[] = [
                    'kuraiyat_game_id' => $game->id,
                    'round_number' => $i + 1,
                    'question_id' => $q->id,
                    'status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            KuraiyatRound::insert($rounds);

            $game->update([
                'status' => 'playing',
                'current_round' => 1,
                'total_questions' => $questions->count(),
                'started_at' => $now,
            ]);
        });
    }

    public function getClue(KuraiyatGame $game): array
    {
        $round = $this->getCurrentRound($game);
        if (!$round) {
            return ['status' => 'error', 'message' => 'انتهت اللعبة'];
        }

        $question = $round->question;
        $clueIndex = (int) $round->clue_level;

        $totalHints = 5;
        if ($clueIndex >= $totalHints) {
            return ['status' => 'finished', 'answer' => $question->correctAnswerText];
        }

        $hintColumn = 'hint_' . ($clueIndex + 1);
        $hintText = $question->{$hintColumn};

        $round->update(['clue_level' => $clueIndex + 1]);

        return [
            'status' => 'ok',
            'clue' => $hintText,
            'clue_number' => $clueIndex + 1,
            'total_clues' => $totalHints,
        ];
    }

    public function submitAnswer(KuraiyatGame $game, KuraiyatPlayer $player, int $roundId, string $guess): array
    {
        $round = KuraiyatRound::findOrFail($roundId);
        $question = $round->question;
        $correct = $question->correctAnswerText;

        $isCorrect = ArabicHelper::matches($guess, $correct);

        if ($isCorrect) {
            $clueLevel = (int) $round->clue_level ?: 1;
            $points = match (true) {
                $clueLevel <= 2 => 750,
                $clueLevel <= 4 => 500,
                default => 250,
            };

            $player->increment('score', $points);
            $round->update(['status' => 'finished', 'is_correct' => true]);
            $game->increment('current_round');

            $finished = $game->current_round > $game->total_rounds;
            if ($finished) $this->finishGame($game);

            return [
                'status' => 'correct',
                'points' => $points,
                'answer' => $correct,
                'game_over' => $finished,
            ];
        }

        return ['status' => 'wrong', 'message' => 'إجابة خاطئة، حاول مرة أخرى'];
    }

    public function skip(KuraiyatGame $game): array
    {
        $round = $this->getCurrentRound($game);
        if (!$round) return ['status' => 'error'];

        $clueLevel = (int) $round->clue_level;

        if ($clueLevel < 5) {
            return $this->getClue($game);
        }

        $question = $round->question;
        $round->update(['status' => 'skipped']);
        $game->increment('current_round');

        $finished = $game->current_round > $game->total_rounds;
        if ($finished) $this->finishGame($game);

        return [
            'status' => 'round_skipped',
            'answer' => $question->correctAnswerText,
            'game_over' => $finished,
        ];
    }

    public function getGameState(KuraiyatGame $game): array
    {
        $game->load('players.user', 'rounds.question', 'creator');

        $authUserId = auth()->id();
        $authPlayer = $game->players->firstWhere('user_id', $authUserId);
        $currentRound = $this->getCurrentRound($game);

        return [
            'id' => $game->id,
            'code' => $game->code,
            'type' => $game->type,
            'status' => $game->status,
            'current_round' => $game->current_round,
            'total_rounds' => $game->total_rounds,
            'players' => $game->players->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->user->name,
                'avatar' => $p->user->avatar_url,
                'score' => $p->score,
            ]),
            'scores' => $game->players->map(fn($p) => [
                'name' => $p->user->name,
                'score' => $p->score,
            ])->sortByDesc('score')->values(),
            'current_round_data' => $currentRound ? [
                'id' => $currentRound->id,
                'round_number' => $currentRound->round_number,
                'clue_level' => (int) $currentRound->clue_level,
            ] : null,
            'is_creator' => $game->created_by === $authUserId,
            'is_player' => (bool) $authPlayer,
        ];
    }

    public function getResults(KuraiyatGame $game): array
    {
        $game->load('players.user');
        $scores = $this->getScores($game);
        return [
            'game' => $game,
            'scores' => $scores,
            'winner' => $scores[0] ?? null,
        ];
    }

    private function getCurrentRound(KuraiyatGame $game): ?KuraiyatRound
    {
        if ($game->status !== 'playing' || $game->current_round > $game->total_rounds) {
            return null;
        }
        return KuraiyatRound::where('kuraiyat_game_id', $game->id)
            ->where('round_number', $game->current_round)
            ->with('question')
            ->first();
    }

    private function finishGame(KuraiyatGame $game): void
    {
        $game->update(['status' => 'finished', 'finished_at' => now()]);
    }

    private function getScores(KuraiyatGame $game): array
    {
        return $game->players->map(fn($p) => [
            'name' => $p->user->name,
            'score' => $p->score,
        ])->sortByDesc('score')->values()->toArray();
    }
}
