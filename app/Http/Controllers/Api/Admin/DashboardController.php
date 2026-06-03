<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Admin Dashboard", description="Admin dashboard endpoints")
 */
class DashboardController extends Controller
{
    public function __construct(private StatsService $statsService) {}

    /**
     * @OA\Get(
     *      path="/api/admin/dashboard",
     *      description="Get admin dashboard statistics.",
     *      tags={"Admin Dashboard"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="Dashboard stats"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
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
