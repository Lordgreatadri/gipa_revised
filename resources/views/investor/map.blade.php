<x-layouts.workspace :title="__('Investment map')">
    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Approved and active opportunities inside the map view. Your location stays in the browser and is used only when you ask for nearby records.') }}</p>
        <a href="{{ route('investor.opportunities') }}" class="text-sm font-semibold text-brand-800 dark:text-brand-200">{{ __('Browse the list') }}</a>
    </div>

    <form id="map-filters" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900">
        <div>
            <x-ui.label for="map-region">{{ __('Region') }}</x-ui.label>
            <select id="map-region" name="region" class="form-control">
                <option value="">{{ __('All regions') }}</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->uuid }}">{{ $region->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-ui.label for="map-sector">{{ __('Sector') }}</x-ui.label>
            <select id="map-sector" name="sector" class="form-control">
                <option value="">{{ __('All sectors') }}</option>
                @foreach ($sectors as $sector)
                    <option value="{{ $sector->uuid }}">{{ $sector->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" id="map-apply" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Apply') }}</button>
        </div>
        <div class="flex items-end">
            <button type="button" id="map-locate" class="cta bg-gold-500 text-brand-950 hover:bg-gold-400">{{ __('Use my location') }}</button>
        </div>
    </form>

    <div id="investment-map" class="h-[36rem] overflow-hidden rounded-3xl border border-slate-200 shadow-card dark:border-slate-800" data-geojson="{{ route('investor.map.opportunities') }}" data-nearby="{{ route('investor.map.nearby') }}"></div>
    <p id="map-status" class="mt-3 text-sm text-slate-500" role="status"></p>
</x-layouts.workspace>
