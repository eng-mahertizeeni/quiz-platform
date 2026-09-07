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
    
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

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
