<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvestorAccountController
{
    public function show(Request $request): View
    {
        return view('investor.account', [
            'user' => $request->user(),
        ]);
    }

    public function updatePersonal(Request $request, AuditRecorder $audit): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
        ]);
        $user->forceFill(['name' => $data['name']])->save();
        $audit->record(AuditEntry::for('account.name_updated', $user)->causedBy($user));

        return back()->with('status', 'Personal details saved.');
    }

    public function updateEmail(Request $request, AuditRecorder $audit): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email,'.$user->id],
            'current_password' => ['required', 'current_password'],
        ]);

        if (strcasecmp($user->email, $data['email']) === 0) {
            return back()->with('status', 'That e-mail is already on the account.');
        }

        $user->forceFill([
            'email' => mb_strtolower($data['email']),
            'email_verified_at' => null,
        ])->save();
        $user->sendEmailVerificationNotification();
        $audit->record(AuditEntry::for(IdentityAuditActions::EMAIL_VERIFICATION_RESENT, $user)->causedBy($user));

        return redirect()->route('verification.notice')->with('status', 'Confirm the new e-mail address from the message we sent.');
    }

    public function updatePhone(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/', 'unique:users,phone,'.$user->id],
            'current_password' => ['required', 'current_password'],
        ]);

        if ($user->phone === $data['phone']) {
            return back()->with('status', 'That phone number is already on the account.');
        }

        $user->forceFill([
            'phone' => $data['phone'],
            'phone_verified_at' => null,
        ])->save();

        return redirect()->route('phone.verification.notice')->with('status', 'Verify the new phone number before the investor workspace opens again.');
    }

    public function updatePassword(Request $request, AuditRecorder $audit): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'min:12', Password::defaults()],
        ]);

        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        $audit->record(AuditEntry::for(IdentityAuditActions::PASSWORD_RESET, $user)->causedBy($user));

        return back()->with('status', 'Password updated.');
    }
}
