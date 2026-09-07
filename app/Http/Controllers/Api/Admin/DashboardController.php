<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private StatsService $statsService) {}

    public function index(): JsonResponse
    {
        $stats = $this->statsService->getAdminDashboardStats();
        $categories = $this->statsService->getCategoryStats();
        $hardestQuestions = $this->statsService->getHardestQuestions(5);
        $leaderboard = $this->statsService->getLeaderboard(5);

        return response()->json([
            'stats'              => $stats,
            'categories'         => $categories,
            'hardest_questions'  => $hardestQuestions,
            'leaderboard'        => UserResource::collection($leaderboard),
        ]);
    }
}
