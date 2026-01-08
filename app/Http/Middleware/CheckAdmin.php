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
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa (có Token hợp lệ không?)
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Bạn chưa đăng nhập.'
            ], 401);
        }

        // 2. Lấy thông tin user hiện tại
        $user = Auth::user();

        // 3. Kiểm tra cột 'roles' trong database có phải là 'admin' không
        // Lưu ý: Chữ 'admin' này phải khớp y hệt với chữ trong database của bạn (chữ thường/hoa)
        if ($user->roles !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Tài khoản của bạn không có quyền Admin.'
            ], 403);
        }

        // 4. Nếu thỏa mãn tất cả, cho phép đi tiếp vào Controller
        return $next($request);
    }
}