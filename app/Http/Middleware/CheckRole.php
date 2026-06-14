<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
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

        // --- ĐOẠN CẬP NHẬT CHO KHỚP DB MỚI ---
        // Lấy tên role từ bảng quan hệ (ví dụ: 'admin', 'teacher', 'assistant', 'student')
        $userRole = $user->roleRelation ? $user->roleRelation->name : '';
        
        // Đồng bộ hóa: Nếu trong DB là 'assistant' thì chuyển thành 'ta' giống middleware cũ của bạn
        if ($userRole === 'assistant') {
            $userRole = 'ta';
        }
        // ------------------------------------

        // Kiểm tra xem vai trò của user có nằm trong danh sách được phép không
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Nếu sai vai trò, điều hướng thông minh về đúng phân hệ
        return match($userRole) {
            'admin'   => redirect()->route('admin.accounts.index'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            'ta'      => redirect()->route('ta.dashboard'),
            default   => redirect('/'),
        };
    }
}