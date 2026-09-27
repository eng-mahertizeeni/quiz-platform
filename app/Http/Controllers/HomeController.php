<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GameSession;
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

        return view('user.dashboard', compact('stats', 'activeGames'));
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