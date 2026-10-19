<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPhoneVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $length = (int) config('onboarding.phone_verification.code_length', 6);

        return [
            'code' => ['required', 'string', 'digits:'.$length],
        ];
    }
}
