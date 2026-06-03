<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/register",
     *      description="Register a new user account.",
     *      tags={"Authentication"},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="string", description="User full name"),
     *                  @OA\Property(property="username", type="string", description="Unique username (alpha_dash)"),
     *                  @OA\Property(property="email", type="string", format="email", description="Unique email address"),
     *                  @OA\Property(property="password", type="string", format="password", description="Minimum 8 characters"),
     *                  @OA\Property(property="password_confirmation", type="string", format="password", description="Must match password"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=201, description="User registered successfully"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required'      => 'الاسم مطلوب',
            'username.required'  => 'اسم المستخدم مطلوب',
            'username.unique'    => 'اسم المستخدم محجوز',
            'username.alpha_dash'=> 'اسم المستخدم يجب أن يحتوي على حروف وأرقام وشرطات فقط',
            'email.required'     => 'البريد الإلكتروني مطلوب',
            'email.unique'       => 'البريد الإلكتروني مسجل مسبقاً',
            'password.required'  => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
        ]);

        $token = $user->createToken('mobile-api')->plainTextToken;

        return response()->json([
            'message' => 'تم التسجيل بنجاح',
            'user'    => new UserResource($user),
            'token'   => $token,
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/login",
     *      description="Login with email and password.",
     *      tags={"Authentication"},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="email", type="string", format="email", description="User email"),
     *                  @OA\Property(property="password", type="string", format="password", description="User password"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Login successful"),
     *      @OA\Response(response=422, description="Invalid credentials")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'البريد الإلكتروني مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['هذا الحساب معطل. يرجى التواصل مع الإدارة.'],
            ]);
        }

        $token = $user->createToken('mobile-api')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user'    => new UserResource($user),
            'token'   => $token,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/logout",
     *      description="Logout and revoke current token.",
     *      tags={"Authentication"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="Logged out successfully"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/user",
     *      description="Get the authenticated user's profile.",
     *      tags={"Authentication"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="User profile data"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }
}
