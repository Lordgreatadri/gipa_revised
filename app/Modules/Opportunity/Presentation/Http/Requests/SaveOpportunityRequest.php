<?php

namespace App\Modules\Opportunity\Presentation\Http\Requests;

use App\Modules\Opportunity\Domain\Enums\InvestmentStructure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'sector_id' => ['required', 'uuid'],
            'sub_sector_id' => ['required', 'uuid'],
            'enterprise_type_id' => ['required', 'uuid'],
            'description' => ['required', 'string', 'max:10000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'success_factors' => ['nullable', 'string', 'max:5000'],
            'competitive_advantages' => ['nullable', 'string', 'max:5000'],
            'district_id' => ['required', 'uuid'],
            'location_description' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:4,11.5'],
            'longitude' => ['required', 'numeric', 'between:-3.5,1.5'],
            'investment_amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'currency' => ['required', Rule::in(['GHS', 'USD'])],
            'investment_structure' => ['required', Rule::enum(InvestmentStructure::class)],
            'roi' => ['nullable', 'numeric', 'between:-100,1000'],
            'irr' => ['nullable', 'numeric', 'between:-100,1000'],
            'npv' => ['nullable', 'numeric', 'between:-999999999999,999999999999'],
            'payback_period_months' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'projected_revenue' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'contact_name' => ['required', 'string', 'max:160'],
            'contact_organization' => ['nullable', 'string', 'max:160'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'contact_email' => ['required', 'email', 'max:160'],
        ];
    }
}
