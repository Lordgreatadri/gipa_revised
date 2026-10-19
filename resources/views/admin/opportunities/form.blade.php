@php
    $editing = $opportunity->exists;
@endphp

<x-layouts.workspace :title="$editing ? __('Edit draft') : __('New opportunity')">
    <form method="POST" action="{{ $editing ? route('admin.opportunities.update', $opportunity) : route('admin.opportunities.store') }}" class="grid gap-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <x-ui.card :title="__('Identification')" :description="__('Sector and sub-sector come from the investment catalogue. Project status starts as not started and moves with the workflow.')">
                <div class="grid gap-4 lg:grid-cols-3" x-data="sectorPicker">
                <div id="sector-catalogue" hidden>@json($catalogue)</div>
                <div class="lg:col-span-3">
                    <x-ui.label for="title" required>{{ __('Title') }}</x-ui.label>
                    <x-ui.input name="title" :value="old('title', $opportunity->title)" required maxlength="180" />
                    <x-ui.input-error for="title" />
                </div>
                <div>
                    <x-ui.label for="sector_id" required>{{ __('Sector') }}</x-ui.label>
                    <select id="sector_id" name="sector_id" class="form-control" required data-sector-select x-on:change="filterSubSectors">
                        <option value="">{{ __('Choose a sector') }}</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->uuid }}" @selected(old('sector_id', $opportunity->sector?->uuid) === $sector->uuid)>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                    <x-ui.input-error for="sector_id" />
                </div>
                <div>
                    <x-ui.label for="sub_sector_id" required>{{ __('Sub-sector') }}</x-ui.label>
                    <select id="sub_sector_id" name="sub_sector_id" class="form-control" required data-subsector-select>
                        <option value="">{{ __('Choose a sub-sector') }}</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">{{ __('The list follows the sector you choose.') }}</p>
                    <x-ui.input-error for="sub_sector_id" />
                </div>
                <div>
                    <x-ui.label for="enterprise_type_id" required>{{ __('Enterprise type') }}</x-ui.label>
                    <select id="enterprise_type_id" name="enterprise_type_id" class="form-control" required>
                        <option value="">{{ __('Choose an enterprise type') }}</option>
                        @foreach ($enterpriseTypes as $type)
                            <option value="{{ $type->uuid }}" @selected(old('enterprise_type_id', $opportunity->enterpriseType?->uuid) === $type->uuid)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <x-ui.input-error for="enterprise_type_id" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Location')" :description="__('District officers and field agents can choose only a metropolitan, municipal or district assembly assigned to their office.')">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-ui.label for="district_id" required>{{ __('Metropolitan, municipal or district') }}</x-ui.label>
                    <select id="district_id" name="district_id" class="form-control" required>
                        <option value="">{{ __('Choose a district') }}</option>
                        @foreach ($districts as $region)
                            <optgroup label="{{ $region->name }}">
                                @foreach ($region->districts as $district)
                                    <option value="{{ $district->uuid }}" @selected(old('district_id', $opportunity->district?->uuid) === $district->uuid)>{{ $district->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <x-ui.input-error for="district_id" />
                </div>
                <div class="md:col-span-2">
                    <x-ui.label for="location_description" required>{{ __('Descriptive location') }}</x-ui.label>
                    <x-ui.input name="location_description" :value="old('location_description', $opportunity->location_description)" required maxlength="500" />
                    <x-ui.input-error for="location_description" />
                </div>
                <div>
                    <x-ui.label for="latitude" required>{{ __('Latitude') }}</x-ui.label>
                    <x-ui.input name="latitude" :value="old('latitude', $opportunity->latitude)" required inputmode="decimal" />
                    <x-ui.input-error for="latitude" />
                </div>
                <div>
                    <x-ui.label for="longitude" required>{{ __('Longitude') }}</x-ui.label>
                    <x-ui.input name="longitude" :value="old('longitude', $opportunity->longitude)" required inputmode="decimal" />
                    <x-ui.input-error for="longitude" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Description')">
            <div class="grid gap-4">
                <div>
                    <x-ui.label for="description" required>{{ __('Overview') }}</x-ui.label>
                    <textarea id="description" name="description" rows="5" required class="form-control">{{ old('description', $opportunity->description) }}</textarea>
                    <x-ui.input-error for="description" />
                </div>
                <div>
                    <x-ui.label for="objectives">{{ __('Main objectives') }}</x-ui.label>
                    <textarea id="objectives" name="objectives" rows="3" class="form-control">{{ old('objectives', $opportunity->objectives) }}</textarea>
                    <x-ui.input-error for="objectives" />
                </div>
                <div>
                    <x-ui.label for="success_factors">{{ __('Critical success factors') }}</x-ui.label>
                    <textarea id="success_factors" name="success_factors" rows="3" class="form-control">{{ old('success_factors', $opportunity->success_factors) }}</textarea>
                    <x-ui.input-error for="success_factors" />
                </div>
                <div>
                    <x-ui.label for="competitive_advantages">{{ __('Competitive advantages') }}</x-ui.label>
                    <textarea id="competitive_advantages" name="competitive_advantages" rows="3" class="form-control">{{ old('competitive_advantages', $opportunity->competitive_advantages) }}</textarea>
                    <x-ui.input-error for="competitive_advantages" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Investment')">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-ui.label for="investment_amount" required>{{ __('Investment amount') }}</x-ui.label>
                    <x-ui.input name="investment_amount" :value="old('investment_amount', $opportunity->investment_amount)" required inputmode="decimal" />
                    <x-ui.input-error for="investment_amount" />
                </div>
                <div>
                    <x-ui.label for="currency" required>{{ __('Currency') }}</x-ui.label>
                    <select id="currency" name="currency" class="form-control" required>
                        @foreach (['GHS', 'USD'] as $currency)
                            <option value="{{ $currency }}" @selected(old('currency', $opportunity->currency ?? 'GHS') === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                    <x-ui.input-error for="currency" />
                </div>
                <div>
                    <x-ui.label for="investment_structure" required>{{ __('Investment structure') }}</x-ui.label>
                    <select id="investment_structure" name="investment_structure" class="form-control" required>
                        @foreach ($structures as $structure)
                            <option value="{{ $structure->value }}" @selected(old('investment_structure', $opportunity->investment_structure?->value) === $structure->value)>{{ $structure->label() }}</option>
                        @endforeach
                    </select>
                    <x-ui.input-error for="investment_structure" />
                </div>
                <div>
                    <x-ui.label for="payback_period_months">{{ __('Payback period (months)') }}</x-ui.label>
                    <x-ui.input name="payback_period_months" :value="old('payback_period_months', $opportunity->payback_period_months)" inputmode="numeric" />
                    <x-ui.input-error for="payback_period_months" />
                </div>
                <div>
                    <x-ui.label for="roi">{{ __('ROI') }}</x-ui.label>
                    <x-ui.input name="roi" :value="old('roi', $opportunity->roi)" inputmode="decimal" />
                    <x-ui.input-error for="roi" />
                </div>
                <div>
                    <x-ui.label for="irr">{{ __('IRR') }}</x-ui.label>
                    <x-ui.input name="irr" :value="old('irr', $opportunity->irr)" inputmode="decimal" />
                    <x-ui.input-error for="irr" />
                </div>
                <div>
                    <x-ui.label for="npv">{{ __('NPV') }}</x-ui.label>
                    <x-ui.input name="npv" :value="old('npv', $opportunity->npv)" inputmode="decimal" />
                    <x-ui.input-error for="npv" />
                </div>
                <div>
                    <x-ui.label for="projected_revenue">{{ __('Projected revenue') }}</x-ui.label>
                    <x-ui.input name="projected_revenue" :value="old('projected_revenue', $opportunity->projected_revenue)" inputmode="decimal" />
                    <x-ui.input-error for="projected_revenue" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Contact')" :description="__('Kept for GIPA staff. It is not shown on the investor catalogue.')">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-ui.label for="contact_name" required>{{ __('Contact name') }}</x-ui.label>
                    <x-ui.input name="contact_name" :value="old('contact_name', $opportunity->contact_name)" required maxlength="160" />
                    <x-ui.input-error for="contact_name" />
                </div>
                <div>
                    <x-ui.label for="contact_organization">{{ __('Organization') }}</x-ui.label>
                    <x-ui.input name="contact_organization" :value="old('contact_organization', $opportunity->contact_organization)" maxlength="160" />
                    <x-ui.input-error for="contact_organization" />
                </div>
                <div>
                    <x-ui.label for="contact_phone" required>{{ __('Contact number') }}</x-ui.label>
                    <x-ui.input name="contact_phone" :value="old('contact_phone', $opportunity->contact_phone)" required maxlength="32" />
                    <x-ui.input-error for="contact_phone" />
                </div>
                <div>
                    <x-ui.label for="contact_email" required>{{ __('Email') }}</x-ui.label>
                    <x-ui.input name="contact_email" type="email" :value="old('contact_email', $opportunity->contact_email)" required maxlength="160" />
                    <x-ui.input-error for="contact_email" />
                </div>
            </div>
        </x-ui.card>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <x-ui.button variant="gold">{{ $editing ? __('Save draft') : __('Create draft') }}</x-ui.button>
            <a href="{{ $editing ? route('admin.opportunities.show', $opportunity) : route('admin.opportunities.index') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-layouts.workspace>
