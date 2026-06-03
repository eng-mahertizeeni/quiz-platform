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

    /**
     * @OA\Get(
     *      path="/api/leaderboard",
     *      description="Get top players leaderboard.",
     *      tags={"Leaderboard"},
     *      @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer"), description="Number of top players"),
     *      @OA\Response(response=200, description="Leaderboard data"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);
        $users = $this->statsService->getLeaderboard($limit);

        return response()->json([
            'leaderboard' => UserResource::collection($users),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/leaderboard/statistics",
     *      description="Get platform statistics including hardest questions and most played categories.",
     *      tags={"Leaderboard"},
     *      @OA\Response(response=200, description="Statistics data"),
     * )
     */
    public function statistics(): JsonResponse
    {
        $hardestQuestions = $this->statsService->getHardestQuestions();
        $mostPlayedCategories = $this->statsService->getMostPlayedCategories();

        return response()->json([
            'hardest_questions'     => $hardestQuestions,
            'most_played_categories' => $mostPlayedCategories,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/leaderboard/categories",
     *      description="Get categories ranked by times played.",
     *      tags={"Leaderboard"},
     *      @OA\Response(response=200, description="Category stats"),
     * )
     */
    public function categoryStats(): JsonResponse
    {
        $categories = $this->statsService->getCategoryStats();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/leaderboard/user-stats",
     *      description="Get authenticated user's personal statistics.",
     *      tags={"Leaderboard"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="User stats"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function userStats(): JsonResponse
    {
        $stats = $this->statsService->getUserStats(request()->user());

        return response()->json([
            'stats' => $stats,
        ]);
    }
}
