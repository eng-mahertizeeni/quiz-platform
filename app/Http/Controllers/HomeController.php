<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GameSession;
use App\Models\UserQuestionAnswer;
use App\Services\StatsService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(private StatsService $statsService) {}

    public function index()
    {
        $featuredCategories = Category::active()->featured()->withCount('questions')->orderBy('sort_order')->limit(8)->get();
        $leaderboard = $this->statsService->getLeaderboard(5);
        $mostPlayedCategories = $this->statsService->getMostPlayedCategories(6);

        if (Auth::check()) {
            $user = Auth::user();
            $answeredCounts = UserQuestionAnswer::where('user_id', $user->id)
                ->whereHas('question', fn($q) => $q->active())
                ->with('question.category')
                ->get()
                ->groupBy(fn($a) => $a->question->category_id)
                ->map(fn($items) => $items->count());

            foreach ($featuredCategories as $cat) {
                $cat->answered_count = $answeredCounts->get($cat->id, 0);
                $cat->progress = $cat->questions_count > 0
                    ? round(($cat->answered_count / $cat->questions_count) * 100, 1)
                    : 0;
            }
        }

        return view('home', compact('featuredCategories', 'leaderboard', 'mostPlayedCategories'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        $stats = $this->statsService->getUserStats($user);
        $activeGames = GameSession::where('created_by', $user->id)
            ->whereIn('status', ['waiting', 'active'])
            ->with('teams')
            ->latest()
            ->get();

        $categories = Category::active()
            ->withCount(['questions' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        $answeredCounts = UserQuestionAnswer::where('user_id', $user->id)
            ->whereHas('question', fn($q) => $q->active())
            ->with('question.category')
            ->get()
            ->groupBy(fn($a) => $a->question->category_id)
            ->map(fn($items) => $items->count());

        foreach ($categories as $cat) {
            $cat->answered_count = $answeredCounts->get($cat->id, 0);
            $cat->progress = $cat->questions_count > 0
                ? round(($cat->answered_count / $cat->questions_count) * 100, 1)
                : 0;
        }

        return view('user.dashboard', compact('stats', 'activeGames', 'categories'));
    }

    public function leaderboard()
    {
        $users = $this->statsService->getLeaderboard(50);
        $categories = $this->statsService->getCategoryStats();
        return view('user.leaderboard', compact('users', 'categories'));
    }

    public function statistics()
    {
        $hardestQuestions = $this->statsService->getHardestQuestions();
        $mostPlayedCategories = $this->statsService->getMostPlayedCategories();
        $leaderboard = $this->statsService->getLeaderboard(10);
        return view('user.statistics', compact('hardestQuestions', 'mostPlayedCategories', 'leaderboard'));
    }
}