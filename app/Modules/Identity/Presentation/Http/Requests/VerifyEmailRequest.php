<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

/**
 * UUID-based counterpart of Laravel's EmailVerificationRequest. The route is
 * additionally protected by the `signed` middleware, so the parameters
 * cannot be tampered with, and by `auth`, so the link only completes for the
 * account that is logged in.
 */
class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if (! hash_equals((string) $user->getRouteKey(), (string) $this->route('uuid'))) {
            return false;
        }

        return hash_equals(sha1($user->getEmailForVerification()), (string) $this->route('hash'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function fulfill(): void
    {
        $user = $this->user();

        if ($user->hasVerifiedEmail()) {
            return;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }
}
