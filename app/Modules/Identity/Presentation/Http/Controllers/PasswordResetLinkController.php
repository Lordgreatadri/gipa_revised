<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\PasswordReset\SendPasswordResetLink;
use App\Modules\Identity\Presentation\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordResetLinkController
{
    public function create(): View
    {
        return view('identity::auth.forgot-password', [
            'expireMinutes' => (int) config('auth.passwords.users.expire', 60),
        ]);
    }

    /**
     * Always answers with the same status so account existence is not
     * disclosed. Abuse is bounded by the named rate limiter on the route
     * and the broker's per-account throttle.
     */
    public function store(ForgotPasswordRequest $request, SendPasswordResetLink $sendLink): RedirectResponse
    {
        $sendLink->handle($request->validated('email'));

        return redirect()->route('password.request')->with('status', 'reset-link-sent');
    }
}
