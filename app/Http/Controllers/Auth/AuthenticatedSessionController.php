<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Kiểm tra nếu đã sai từ 2 lần trở lên thì bắt buộc kiểm tra mã captcha trước
        if (session('login_attempts', 0) >= 2) {
            $request->validate([
                'captcha' => 'required|string',
            ], [
                'captcha.required' => 'Vui lòng nhập mã captcha.',
            ]);

            if ($request->captcha !== session('captcha_code')) {
                return back()->withErrors(['captcha' => 'Mã captcha không chính xác.'])->withInput();
            }
        }

        try {
            $request->authenticate();
            $request->session()->regenerate();

            // Đăng nhập thành công -> Xóa lịch sử đếm lỗi
            session()->forget('login_attempts');

            // Lấy thông tin user vừa đăng nhập thành công
            $user = auth()->user();

            // Phân luồng điều hướng thông minh dựa vào role_id
            if ($user->role_id == 1) { 
                return redirect()->route('admin.accounts.index');
            }
            
            if ($user->role_id == 2) { 
                return redirect()->route('teacher.dashboard');
            }
            
            if ($user->role_id == 3) { 
                return redirect()->route('ta.dashboard');
            }

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (ValidationException $e) {
            // Đăng nhập thất bại -> Tăng số lần sai lên 1 và quăng tiếp lỗi ra cho Laravel xử lý
            session(['login_attempts' => session('login_attempts', 0) + 1]);
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
