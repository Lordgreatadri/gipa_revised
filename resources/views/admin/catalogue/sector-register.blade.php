<x-layouts.workspace :title="__('Sector records')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Add or edit a sector. A code uses capital letters, numbers and hyphens. Remove is available only when nothing uses the sector.') }}</p>
        <a href="{{ route('admin.sectors.index') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Overview') }}</a>
    </div>

    <div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Sectors') }}</h2>
            <form method="POST" action="{{ route('admin.catalogue.sectors.store') }}" class="mt-4 grid gap-3 sm:grid-cols-[8rem_minmax(0,1fr)_auto] sm:items-end">
                @csrf
                <div>
                    <x-ui.label for="sector_code" required>{{ __('Code') }}</x-ui.label>
                    <x-ui.input id="sector_code" name="code" maxlength="32" required :value="old('_record') ? '' : old('code')" />
                </div>
                <div>
                    <x-ui.label for="sector_name" required>{{ __('Name') }}</x-ui.label>
                    <x-ui.input id="sector_name" name="name" maxlength="160" required :value="old('_record') ? '' : old('name')" />
                </div>
                <x-ui.button>{{ __('Add sector') }}</x-ui.button>
            </form>
            @if ($errors->any() && old('_record') === null)
                <x-ui.input-error for="code" />
                <x-ui.input-error for="name" />
            @endif
            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($sectors as $sector)
                    <li class="py-3 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $sector->name }}</span>
                                @unless ($sector->is_active)
                                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('Inactive') }}</span>
                                @endunless
                                <span class="mt-1 block text-xs text-slate-500">{{ $sector->code }} · {{ $sector->sub_sectors_count }} {{ __('sub-sectors') }} · {{ $sector->opportunities_count }} {{ __('opportunities') }}</span>
                            </span>
                            <div class="flex shrink-0 items-center gap-3">
                                @if ($sector->sub_sectors_count === 0 && $sector->opportunities_count === 0)
                                    <form method="POST" action="{{ route('admin.catalogue.sectors.destroy', $sector) }}">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700 dark:text-red-300" type="submit">{{ __('Remove') }}</button></form>
                                @endif
                            </div>
                        </div>
                        <details class="mt-2" @if (session('editing') === $sector->uuid) open @endif>
                            <summary class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-brand-700 dark:text-brand-300"><i data-lucide="pencil" class="size-3.5"></i>{{ __('Edit') }}</summary>
                            <form method="POST" action="{{ route('admin.catalogue.sectors.update', $sector) }}" class="mt-3 grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_record" value="{{ $sector->uuid }}">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <x-ui.label for="sector-code-{{ $sector->uuid }}" required>{{ __('Code') }}</x-ui.label>
                                        <x-ui.input id="sector-code-{{ $sector->uuid }}" name="code" maxlength="32" required :value="session('editing') === $sector->uuid ? old('code', $sector->code) : $sector->code" />
                                    </div>
                                    <div>
                                        <x-ui.label for="sector-name-{{ $sector->uuid }}" required>{{ __('Name') }}</x-ui.label>
                                        <x-ui.input id="sector-name-{{ $sector->uuid }}" name="name" maxlength="160" required :value="session('editing') === $sector->uuid ? old('name', $sector->name) : $sector->name" />
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label for="sector-description-{{ $sector->uuid }}">{{ __('Description') }}</x-ui.label>
                                    <textarea id="sector-description-{{ $sector->uuid }}" name="description" maxlength="500" rows="2" class="form-control">{{ session('editing') === $sector->uuid ? old('description', $sector->description) : $sector->description }}</textarea>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" name="is_active" value="1" @checked(session('editing') === $sector->uuid ? old('is_active') : $sector->is_active)>
                                    {{ __('Active') }}
                                </label>
                                @if (session('editing') === $sector->uuid)
                                    <x-ui.input-error for="code" />
                                    <x-ui.input-error for="name" />
                                @endif
                                <div class="sm:max-w-40"><x-ui.button>{{ __('Save sector') }}</x-ui.button></div>
                            </form>
                        </details>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.workspace>
