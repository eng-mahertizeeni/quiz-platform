<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Admin Users", description="Admin user management endpoints")
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/admin/users",
     *      description="List all users.",
     *      tags={"Admin Users"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="role", in="query", @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="List of users"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withTrashed();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'users'      => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/admin/users/{user}",
     *      description="Get user details with game sessions and submissions.",
     *      tags={"Admin Users"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="User details"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['gameSessions', 'submittedQuestions.category']);

        return response()->json([
            'user' => new UserResource($user),
            'game_sessions' => $user->gameSessions,
            'submitted_questions' => $user->submittedQuestions,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/users/{user}/toggle-status",
     *      description="Toggle active/inactive status of a user.",
     *      tags={"Admin Users"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Status toggled"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function toggleStatus(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'لا يمكن تعطيل حساب المسؤول'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message'   => 'تم تحديث حالة الحساب',
            'is_active' => $user->fresh()->is_active,
        ]);
    }

    /**
     * @OA\Delete(
     *      path="/api/admin/users/{user}",
     *      description="Delete a user account.",
     *      tags={"Admin Users"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="User deleted"),
     *      @OA\Response(response=403, description="Forbidden - Cannot delete admin"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'لا يمكن حذف حساب المسؤول'], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'تم حذف المستخدم',
        ]);
    }
}
