<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/profile",
     *      description="Get authenticated user's profile details.",
     *      tags={"Profile"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="Profile data"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/profile",
     *      description="Update user profile information.",
     *      tags={"Profile"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="string", description="User full name"),
     *                  @OA\Property(property="username", type="string", description="Unique username (alpha_dash)"),
     *                  @OA\Property(property="email", type="string", format="email", description="Unique email address"),
     *                  @OA\Property(property="avatar", type="string", format="binary", description="Avatar image (jpg,jpeg,png,webp max 2MB)"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Profile updated"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/profile/password",
     *      description="Update user password.",
     *      tags={"Profile"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="current_password", type="string", format="password", description="Current password"),
     *                  @OA\Property(property="password", type="string", format="password", description="New password (min 8 chars)"),
     *                  @OA\Property(property="password_confirmation", type="string", format="password", description="Confirm new password"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Password updated"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة',
            'password.min'                       => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed'                 => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }

    /**
     * @OA\Delete(
     *      path="/api/profile",
     *      description="Delete the authenticated user's account.",
     *      tags={"Profile"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="Account deleted"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'تم حذف حسابك بنجاح',
        ]);
    }
}
