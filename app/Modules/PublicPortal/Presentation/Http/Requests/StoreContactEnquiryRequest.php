<?php

namespace App\Modules\PublicPortal\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreContactEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email', ''))),
            'name' => trim((string) $this->input('name', '')),
            'subject' => trim((string) $this->input('subject', '')),
            'phone_number' => trim((string) $this->input('phone_number', '')),
            'available_time_for_call' => trim((string) $this->input('available_time_for_call', '')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'available_time_for_call' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $phone = (string) $this->input('phone_number', '');

            if ($phone === '') {
                return;
            }

            $digits = preg_replace('/\D/', '', $phone) ?? '';

            if (strlen($digits) < 7 || strlen($digits) > 15) {
                $validator->errors()->add('phone_number', 'Enter a valid phone number, for example 024 123 4567.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent.accepted' => 'Please confirm you consent to GIPA using your details to respond.',
            'website.prohibited' => 'The enquiry could not be submitted.',
        ];
    }

    /**
     * @return array{name: string, email: string, phone_number: ?string, available_time_for_call: ?string, subject: string, message: string}
     */
    public function enquiryPayload(): array
    {
        /** @var array{name: string, email: string, phone_number: ?string, available_time_for_call: ?string, subject: string, message: string} $payload */
        $payload = $this->safe()->only([
            'name',
            'email',
            'phone_number',
            'available_time_for_call',
            'subject',
            'message',
        ]);

        return $payload;
    }
}
