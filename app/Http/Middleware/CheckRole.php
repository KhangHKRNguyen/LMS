<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // Đổi string $role thành ...$roles để nhận diện được mảng vai trò
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->status === 'inactive') {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.',
            ]);
        }

        // Kiểm tra xem vai trò của user có nằm trong danh sách được phép không
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Nếu sai vai trò, điều hướng thông minh về đúng phân hệ
        return match($user->role) {
            'admin'   => redirect()->route('admin.accounts.index'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            'ta'      => redirect()->route('ta.dashboard'),
            default   => redirect('/'),
        };
    }
}