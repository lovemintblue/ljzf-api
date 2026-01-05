<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查用户是否已认证
        if (Auth::check()) {
            $user = Auth::user();

            // 检查用户状态是否被禁用 (status = 0)
            if ($user->status == 0) {
                return response()->json([
                    'message' => '您的账户已被禁用，无法访问该功能！',
                    'error' => 'USER_DISABLED'
                ], 403);
            }
        }

        return $next($request);
    }
}
