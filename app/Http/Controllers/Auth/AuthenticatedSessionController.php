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
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Kiểm tra tài khoản bị khóa
        if (auth()->user()->status === 'inactive') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.',
            ]);
        }

        $request->session()->regenerate();

        // Điều hướng theo vai trò
        return match(auth()->user()->role) {
            'admin'   => redirect()->route('admin.accounts.index'),

            'teacher' => redirect()->route('teacher.dashboard'),

            'student' => redirect()->route('student.dashboard'),

            'ta'      => redirect()->route('ta.dashboard'),

            default   => redirect('/'),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
