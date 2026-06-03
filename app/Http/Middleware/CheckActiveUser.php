<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class CheckActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json(['error' => 'تم تعطيل حسابك'], 403);
            }

            return redirect()->route('login')
                ->with('error', 'تم تعطيل حسابك. يرجى التواصل مع الإدارة.');
        }

        return $next($request);
    }
}