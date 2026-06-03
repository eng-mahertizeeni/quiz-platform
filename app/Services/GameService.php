<?php

namespace App\Services;

use App\Models\Category;
use App\Models\GameAnswer;
use App\Models\GameRound;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\PowerUsage;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class GameService
{
    public function createSession(int $userId, array $data): GameSession
    {
        return DB::transaction(function () use ($userId, $data) {
            $session = GameSession::create([
                'created_by' => $userId,
                'status' => 'waiting',
                'timer_seconds' => $data['timer_seconds'] ?? 30,
                'sound_enabled' => $data['sound_enabled'] ?? true,
                'total_questions' => 0,
            ]);

            $teamOne = GameTeam::create([
                'game_session_id' => $session->id,
                'name' => $data['team_one_name'],
                'color' => $data['team_one_color'] ?? '#3B82F6',
            ]);

            GameTeam::create([
                'game_session_id' => $session->id,
                'name' => $data['team_two_name'],
                'color' => $data['team_two_color'] ?? '#EF4444',
            ]);

            $session->update(['current_team_id' => $teamOne->id]);

            return $session->fresh();
        });
    }

    public function attachCategories(GameSession $session, array $categoryIds): void
    {
        DB::transaction(function () use ($session, $categoryIds) {
            $pivot = [];
            foreach ($categoryIds as $order => $categoryId) {
                $pivot[$categoryId] = ['sort_order' => $order];
            }
            $session->categories()->sync($pivot);
            $this->generateRounds($session);
            $totalQuestions = $session->rounds()->count();
            $session->update(['total_questions' => $totalQuestions]);
        });
    }

    private function generateRounds(GameSession $session): void
    {
        $session->load(['categories', 'teams']);
        $teams = $session->teams;
        $roundNumber = 1;

        foreach ($session->categories as $category) {
            $difficulties = [
                ['difficulty' => 'medium', 'points' => 250],
                ['difficulty' => 'hard', 'points' => 500],
                ['difficulty' => 'very_hard', 'points' => 750],
            ];

            foreach ($difficulties as $level) {
                $questions = Question::where('category_id', $category->id)
                    ->where('difficulty', $level['difficulty'])
                    ->where('status', 'active')
                    ->inRandomOrder()
                    ->limit(2)
                    ->get();

                foreach ($questions as $index => $question) {
                    $assignedTeam = $teams[$index % 2];
                    GameRound::create([
                        'game_session_id' => $session->id,
                        'question_id' => $question->id,
                        'category_id' => $category->id,
                        'assigned_team_id' => $assignedTeam->id,
                        'status' => 'pending',
                        'points_value' => $level['points'],
                        'round_number' => $roundNumber++,
                    ]);
                }
            }
        }
    }

    public function startSession(GameSession $session): void
    {
        $session->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        foreach ($session->categories as $category) {
            $category->increment('times_played');
        }
    }

    public function getBoard(GameSession $session): array
    {
        $session->load(['categories', 'teams', 'rounds.question', 'rounds.answer']);

        $board = [];
        foreach ($session->categories as $category) {
            $categoryRounds = $session->rounds->where('category_id', $category->id);
            $board[] = [
                'category' => $category,
                'rounds' => [
                    250 => $categoryRounds->where('points_value', 250)->values(),
                    500 => $categoryRounds->where('points_value', 500)->values(),
                    750 => $categoryRounds->where('points_value', 750)->values(),
                ],
            ];
        }

        return $board;
    }

    public function answerQuestion(
        GameSession $session,
        GameRound $round,
        GameTeam $team,
        string $selectedAnswer,
        int $timeTaken = 0,
        bool $powerRemoveUsed = false
    ): array {
        return DB::transaction(function () use ($session, $round, $team, $selectedAnswer, $timeTaken, $powerRemoveUsed) {
            $question = $round->question;
            $isCorrect = $selectedAnswer === $question->correct_answer;
            $pointsEarned = $isCorrect ? $round->points_value : 0;

            $answer = GameAnswer::create([
                'game_session_id' => $session->id,
                'game_round_id' => $round->id,
                'team_id' => $team->id,
                'question_id' => $question->id,
                'selected_answer' => $selectedAnswer,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'time_taken' => $timeTaken,
                'power_remove_used' => $powerRemoveUsed,
            ]);

            $round->update([
                'status' => 'answered',
                'answered_by_team_id' => $team->id,
            ]);

            if ($isCorrect) {
                $team->increment('score', $pointsEarned);
                $team->increment('correct_answers');
            } else {
                $team->increment('wrong_answers');
            }

            $question->increment('times_used');
            if ($isCorrect) {
                $question->increment('times_correct');
            } else {
                $question->increment('times_wrong');
            }

            $session->increment('answered_questions');

            $this->switchTurn($session, $team);

            $isFinished = $this->checkGameFinished($session);

            return [
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'correct_answer' => $question->correct_answer,
                'is_finished' => $isFinished,
            ];
        });
    }

    public function usePowerRemove(GameRound $round, GameTeam $team): array
    {
        $question = $round->question;
        $wrongAnswers = collect(['a', 'b', 'c', 'd'])
            ->reject(fn($k) => $k === $question->correct_answer)
            ->shuffle()
            ->take(2)
            ->values()
            ->toArray();

        $team->update(['power_remove_used' => true]);

        PowerUsage::create([
            'game_session_id' => $round->game_session_id,
            'team_id' => $team->id,
            'game_round_id' => $round->id,
            'power_type' => 'remove_two',
        ]);

        return $wrongAnswers;
    }

    public function usePowerSteal(GameSession $session, GameRound $round, GameTeam $stealingTeam): GameRound
    {
        $round->update([
            'assigned_team_id' => $stealingTeam->id,
            'is_stolen' => true,
        ]);

        $stealingTeam->update(['power_steal_used' => true]);

        PowerUsage::create([
            'game_session_id' => $session->id,
            'team_id' => $stealingTeam->id,
            'game_round_id' => $round->id,
            'power_type' => 'steal_question',
        ]);

        return $round->fresh();
    }

    private function switchTurn(GameSession $session, GameTeam $currentTeam): void
    {
        $teams = $session->teams;
        $otherTeam = $teams->first(fn($t) => $t->id !== $currentTeam->id);
        $session->update(['current_team_id' => $otherTeam->id]);
    }

    private function checkGameFinished(GameSession $session): bool
    {
        $pendingRounds = GameRound::where('game_session_id', $session->id)
            ->whereIn('status', ['pending', 'active'])
            ->count();

        if ($pendingRounds === 0) {
            $this->finishGame($session);
            return true;
        }

        return false;
    }

    public function finishGame(GameSession $session): void
    {
        $session->load('teams');
        $winner = $session->teams->sortByDesc('score')->first();

        $winner->update(['is_winner' => true]);

        $session->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        $creator = $session->creator;
        $creator->increment('games_played');

        if ($winner->name === $session->teams->first()->name) {
            $creator->increment('games_won');
            $creator->increment('total_score', $winner->score);
        }
    }
}