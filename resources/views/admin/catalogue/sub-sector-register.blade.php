<x-layouts.workspace :title="__('Sub-sector records')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('A sub-sector stays in its sector once an opportunity uses it. Open Edit to change the name, code or description.') }}</p>
        <a href="{{ route('admin.sub-sectors.index') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Overview') }}</a>
    </div>

    <div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Sub-sectors') }}</h2>
            <form method="POST" action="{{ route('admin.catalogue.sub-sectors.store') }}" class="mt-4 grid gap-3">
                @csrf
                <div>
                    <x-ui.label for="sector_id" required>{{ __('Sector') }}</x-ui.label>
                    <select id="sector_id" name="sector_id" class="form-control" required>
                        <option value="">{{ __('Choose a sector') }}</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->uuid }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-[8rem_minmax(0,1fr)_auto] sm:items-end">
                    <div>
                        <x-ui.label for="sub_code" required>{{ __('Code') }}</x-ui.label>
                        <x-ui.input id="sub_code" name="code" maxlength="32" required />
                    </div>
                    <div>
                        <x-ui.label for="sub_name" required>{{ __('Name') }}</x-ui.label>
                        <x-ui.input id="sub_name" name="name" maxlength="160" required />
                    </div>
                    <x-ui.button>{{ __('Add sub-sector') }}</x-ui.button>
                </div>
            </form>
            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($subSectors as $subSector)
                    <li class="py-3 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $subSector->name }}</span>
                                @unless ($subSector->is_active)
                                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('Inactive') }}</span>
                                @endunless
                                <span class="mt-1 block text-xs text-slate-500">{{ $subSector->sector?->name }} · {{ $subSector->code }} · {{ $subSector->opportunities_count }} {{ __('opportunities') }}</span>
                            </span>
                            @if ($subSector->opportunities_count === 0)
                                <form method="POST" action="{{ route('admin.catalogue.sub-sectors.destroy', $subSector) }}">@csrf @method('DELETE')<button class="shrink-0 text-sm font-semibold text-red-700 dark:text-red-300" type="submit">{{ __('Remove') }}</button></form>
                            @endif
                        </div>
                        <details class="mt-2" @if (session('editing') === $subSector->uuid) open @endif>
                            <summary class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-brand-700 dark:text-brand-300"><i data-lucide="pencil" class="size-3.5"></i>{{ __('Edit') }}</summary>
                            <form method="POST" action="{{ route('admin.catalogue.sub-sectors.update', $subSector) }}" class="mt-3 grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_record" value="{{ $subSector->uuid }}">
                                <div>
                                    <x-ui.label for="sub-sector-{{ $subSector->uuid }}" required>{{ __('Sector') }}</x-ui.label>
                                    <select id="sub-sector-{{ $subSector->uuid }}" name="sector_id" class="form-control" required @disabled($subSector->opportunities_count > 0)>
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->uuid }}" @selected($sector->id === $subSector->sector_id)>{{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($subSector->opportunities_count > 0)
                                        <input type="hidden" name="sector_id" value="{{ $subSector->sector?->uuid }}">
                                    @endif
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <x-ui.label for="sub-code-{{ $subSector->uuid }}" required>{{ __('Code') }}</x-ui.label>
                                        <x-ui.input id="sub-code-{{ $subSector->uuid }}" name="code" maxlength="32" required :value="session('editing') === $subSector->uuid ? old('code', $subSector->code) : $subSector->code" />
                                    </div>
                                    <div>
                                        <x-ui.label for="sub-name-{{ $subSector->uuid }}" required>{{ __('Name') }}</x-ui.label>
                                        <x-ui.input id="sub-name-{{ $subSector->uuid }}" name="name" maxlength="160" required :value="session('editing') === $subSector->uuid ? old('name', $subSector->name) : $subSector->name" />
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label for="sub-description-{{ $subSector->uuid }}">{{ __('Description') }}</x-ui.label>
                                    <textarea id="sub-description-{{ $subSector->uuid }}" name="description" maxlength="500" rows="2" class="form-control">{{ session('editing') === $subSector->uuid ? old('description', $subSector->description) : $subSector->description }}</textarea>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" name="is_active" value="1" @checked(session('editing') === $subSector->uuid ? old('is_active') : $subSector->is_active)>
                                    {{ __('Active') }}
                                </label>
                                @if (session('editing') === $subSector->uuid)
                                    <x-ui.input-error for="code" />
                                    <x-ui.input-error for="name" />
                                    <x-ui.input-error for="sector_id" />
                                @endif
                                <div class="sm:max-w-48"><x-ui.button>{{ __('Save sub-sector') }}</x-ui.button></div>
                            </form>
                        </details>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.workspace>
