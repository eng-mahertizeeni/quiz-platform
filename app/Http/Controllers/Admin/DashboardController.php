<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatsService;

class DashboardController extends Controller
{
    public function __construct(private StatsService $statsService) {}

    public function index()
    {
        $stats = $this->statsService->getAdminDashboardStats();
        $categories = $this->statsService->getCategoryStats();
        $hardestQuestions = $this->statsService->getHardestQuestions(5);
        $leaderboard = $this->statsService->getLeaderboard(5);

        return view('admin.dashboard', compact('stats', 'categories', 'hardestQuestions', 'leaderboard'));
    }
}