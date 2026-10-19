<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Application\PostLoginDestination;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Presentation\Http\Requests\VerifyEmailRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController
{
    public function notice(Request $request, PostLoginDestination $destination): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->to($destination->for($user));
        }

        return view('identity::auth.verify-email');
    }

    public function verify(VerifyEmailRequest $request, PostLoginDestination $destination): RedirectResponse
    {
        $request->fulfill();

        /** @var User $user */
        $user = $request->user();

        return redirect()->to($destination->for($user))->with('status', 'email-verified');
    }

    public function resend(Request $request, AuditRecorder $audit, PostLoginDestination $destination): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->to($destination->for($user));
        }

        $user->sendEmailVerificationNotification();

        $audit->record(AuditEntry::for(IdentityAuditActions::EMAIL_VERIFICATION_RESENT, $user)->causedBy($user));

        return back()->with('status', 'verification-link-sent');
    }
}
