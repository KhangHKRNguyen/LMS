<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
        $request->authenticate();

        $request->session()->regenerate();

        // 1. Lấy thông tin user vừa đăng nhập thành công
        $user = auth()->user();

        // 2. Phân luồng điều hướng thông minh dựa vào role_id (Khớp với Seeder của bạn)
        if ($user->role_id == 1) { // 1 là Admin
            return redirect()->route('admin.accounts.index');
        }
        
        if ($user->role_id == 2) { // 2 là Teacher
            return redirect()->route('teacher.dashboard');
        }
        
        if ($user->role_id == 3) { // 3 là Assistant/TA
            return redirect()->route('ta.dashboard');
        }

        // Mặc định cho học viên (role_id = 4) hoặc các trường hợp khác
        return redirect()->intended(route('dashboard', absolute: false));
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
