<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Thêm validate trường 'captcha'
        $request->validate([
            'email' => ['required', 'email'],
            'captcha' => ['required', 'string'],
        ], [
            'captcha.required' => 'Vui lòng nhập mã captcha.',
        ]);

        // Kiểm tra tính chính xác của mã captcha
        if ($request->captcha !== session('captcha_code')) {
            return back()->withErrors(['captcha' => 'Mã captcha không chính xác.'])->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }
}
