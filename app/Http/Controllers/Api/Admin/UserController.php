<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
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

    public function show(User $user): JsonResponse
    {
        $user->load(['gameSessions', 'submittedQuestions.category']);

        return response()->json([
            'user' => new UserResource($user),
            'game_sessions' => $user->gameSessions,
            'submitted_questions' => $user->submittedQuestions,
        ]);
    }

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
