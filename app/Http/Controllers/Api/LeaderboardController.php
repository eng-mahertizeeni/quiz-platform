<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(private StatsService $statsService) {}

    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);
        $users = $this->statsService->getLeaderboard($limit);

        return response()->json([
            'leaderboard' => UserResource::collection($users),
        ]);
    }

    public function statistics(): JsonResponse
    {
        $hardestQuestions = $this->statsService->getHardestQuestions();
        $mostPlayedCategories = $this->statsService->getMostPlayedCategories();

        return response()->json([
            'hardest_questions'     => $hardestQuestions,
            'most_played_categories' => $mostPlayedCategories,
        ]);
    }

    public function categoryStats(): JsonResponse
    {
        $categories = $this->statsService->getCategoryStats();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function userStats(): JsonResponse
    {
        $stats = $this->statsService->getUserStats(request()->user());

        return response()->json([
            'stats' => $stats,
        ]);
    }
}
