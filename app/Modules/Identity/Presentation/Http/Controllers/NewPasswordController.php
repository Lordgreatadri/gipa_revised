<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\PasswordReset\ResetPasswordWithToken;
use App\Modules\Identity\Presentation\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewPasswordController
{
    public function create(Request $request, string $token): View
    {
        return view('identity::auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(ResetPasswordRequest $request, ResetPasswordWithToken $resetPassword): RedirectResponse
    {
        $status = $resetPassword->handle($request->only('email', 'password', 'password_confirmation', 'token'));

        if (ResetPasswordWithToken::succeeded($status)) {
            return redirect()->route('login')->with('status', 'password-reset');
        }

        // The framework status is a translation key (e.g. passwords.token).
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
