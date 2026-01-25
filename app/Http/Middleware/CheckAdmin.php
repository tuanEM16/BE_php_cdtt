<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Bạn chưa đăng nhập.'
            ], 401);
        }
        $user = Auth::user();
        if ($user->roles !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Tài khoản của bạn không có quyền Admin.'
            ], 403);
        }
        return $next($request);
    }
}