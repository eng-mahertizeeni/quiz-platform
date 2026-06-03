<?php

namespace App\Services;

use App\Models\Category;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public function getLeaderboard(int $limit = 10): \Illuminate\Support\Collection
    {
        $data = Cache::flexible('leaderboard_' . $limit, [300, 600], function () use ($limit) {
            return User::where('role', 'user')
                ->where('games_played', '>', 0)
                ->orderByDesc('total_score')
                ->limit($limit)
                ->get()
                ->toArray();
        });

        return User::hydrate($data);
    }

    public function getMostPlayedCategories(int $limit = 10): \Illuminate\Support\Collection
    {
        $data = Cache::flexible('most_played_categories', [600, 900], function () use ($limit) {
            return Category::active()
                ->withCount('questions')
                ->orderByDesc('times_played')
                ->limit($limit)
                ->get()
                ->toArray();
        });

        return Category::hydrate($data);
    }

    public function getHardestQuestions(int $limit = 10): \Illuminate\Support\Collection
    {
        return Question::active()
            ->where('times_used', '>', 5)
            ->orderByRaw('(times_wrong / times_used) DESC')
            ->with('category')
            ->limit($limit)
            ->get();
    }

    public function getAdminDashboardStats(): array
    {
        return Cache::remember('admin_dashboard_stats', 120, function () {
            return [
                'total_users' => User::where('role', 'user')->count(),
                'total_questions' => Question::active()->count(),
                'total_categories' => Category::active()->count(),
                'total_games' => GameSession::finished()->count(),
                'games_today' => GameSession::finished()->whereDate('finished_at', today())->count(),
                'pending_submissions' => \App\Models\SubmittedQuestion::where('status', 'pending')->count(),
                'active_games' => GameSession::active()->count(),
                'new_users_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            ];
        });
    }

    public function getCategoryStats(): \Illuminate\Support\Collection
    {
        return Category::active()
            ->withCount(['questions' => fn($q) => $q->active()])
            ->orderByDesc('times_played')
            ->get();
    }

    public function getUserStats(User $user): array
    {
        return [
            'games_played' => $user->games_played,
            'games_won' => $user->games_won,
            'win_rate' => $user->win_rate,
            'total_score' => $user->total_score,
            'submitted_questions' => $user->submittedQuestions()->count(),
            'approved_questions' => $user->submittedQuestions()->where('status', 'approved')->count(),
            'recent_games' => GameSession::where('created_by', $user->id)
                ->finished()
                ->with(['teams', 'categories'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}