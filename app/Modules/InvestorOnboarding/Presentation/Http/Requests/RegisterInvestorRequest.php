<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Requests;

use App\Modules\InvestorOnboarding\Application\Registration\RegisterInvestorData;
use App\Modules\Shared\Domain\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * D-2 minimum fields. The country is submitted as its uuid (ADR-0003: no
 * numeric ids on the wire). Any `user_type` or role input is ignored: it is
 * not part of the validated payload and the use case forces "investor".
 */
class RegisterInvestorRequest extends FormRequest
{
    public const PHONE_E164 = '/^\+[1-9]\d{6,14}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The form posts the number in two parts (dial-code country uuid +
     * national number) so the UI can show flags; the API-style single `phone`
     * field is still accepted. Either way the E.164 `phone` value below is
     * what gets validated and stored.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email', ''))),
            'phone' => $this->composePhone(),
        ]);
    }

    private function composePhone(): string
    {
        $direct = preg_replace('/[\s\-().]/', '', (string) $this->input('phone', ''));

        if ($direct !== '') {
            return $direct;
        }

        $national = preg_replace('/\D/', '', (string) $this->input('phone_number', ''));
        $countryUuid = (string) $this->input('phone_country', '');

        if ($national === '' || ! Str::isUuid($countryUuid)) {
            return '';
        }

        $dialCode = Country::query()->where('uuid', $countryUuid)->value('code');

        if (! is_string($dialCode) || $dialCode === '') {
            return '';
        }

        // Drop the national trunk prefix ("0") so +233 + 020… becomes +23320…
        return $dialCode.ltrim($national, '0');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone_country' => ['nullable', 'uuid', 'exists:countries,uuid'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_E164, 'unique:users,phone'],
            'country' => ['required', 'uuid', 'exists:countries,uuid'],
            'password' => ['required', 'string', 'confirmed', 'min:12', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => __('Choose a country code and enter your mobile phone number.'),
            'phone.regex' => __('Enter a valid mobile phone number, e.g. +233201234567.'),
            'phone.unique' => __('An account with this phone number already exists.'),
            'terms.accepted' => __('You must accept the terms of use to register.'),
        ];
    }

    public function toData(): RegisterInvestorData
    {
        $validated = $this->validated();

        return new RegisterInvestorData(
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'],
            password: $validated['password'],
            country: Country::query()->where('uuid', $validated['country'])->firstOrFail(),
        );
    }
}
