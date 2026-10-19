<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Controllers;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\ConfirmPhoneVerification;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\RequestPhoneVerification;
use App\Modules\InvestorOnboarding\Domain\Exceptions\InvalidPhoneVerificationCode;
use App\Modules\InvestorOnboarding\Domain\Exceptions\PhoneAlreadyVerified;
use App\Modules\InvestorOnboarding\Domain\Exceptions\PhoneVerificationDeliveryFailed;
use App\Modules\InvestorOnboarding\Domain\Rules\VerifiedInvestorRule;
use App\Modules\InvestorOnboarding\Domain\Models\PhoneVerificationCode;
use App\Modules\InvestorOnboarding\Presentation\Http\Requests\ConfirmPhoneVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PhoneVerificationController
{
    public function notice(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedPhone()) {
            return $this->afterVerification($user);
        }

        $hasActiveCode = PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->active()
            ->exists();

        return view('investor-onboarding::verify-phone', [
            'phone' => $user->phone,
            'hasActiveCode' => $hasActiveCode,
            'codeLength' => (int) config('onboarding.phone_verification.code_length', 6),
        ]);
    }

    public function send(Request $request, RequestPhoneVerification $requestVerification): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $requestVerification->handle($user);
        } catch (PhoneAlreadyVerified) {
            return $this->afterVerification($user);
        } catch (PhoneVerificationDeliveryFailed $exception) {
            throw ValidationException::withMessages(['code' => $exception->getMessage()]);
        }

        return redirect()->route('phone.verification.notice')->with('status', 'phone-code-sent');
    }

    public function confirm(ConfirmPhoneVerificationRequest $request, ConfirmPhoneVerification $confirmVerification): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $confirmVerification->handle($user, (string) $request->string('code'));
        } catch (PhoneAlreadyVerified) {
            return $this->afterVerification($user);
        } catch (InvalidPhoneVerificationCode $exception) {
            throw ValidationException::withMessages(['code' => $exception->getMessage()]);
        }

        return $this->afterVerification($user)->with('status', 'phone-verified');
    }

    private function afterVerification(User $user): RedirectResponse
    {
        if (VerifiedInvestorRule::satisfiedBy($user) && $user->investorProfile === null) {
            return redirect()->route('investor.profile');
        }

        return redirect()->route('investor.dashboard');
    }
}
