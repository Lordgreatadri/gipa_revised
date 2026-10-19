<x-layouts.workspace :title="__('Investment catalogue')">
    <p class="mb-6 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Sectors, sub-sectors and enterprise types used on opportunity records. A sub-sector always belongs to one sector, so healthcare cannot offer an agriculture activity. Open Edit on a row to change its name, code, or description.') }}</p>

    <div class="grid gap-6 xl:grid-cols-2">
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
            <ul class="mt-4 max-h-[36rem] divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Enterprise types') }}</h2>
            <form method="POST" action="{{ route('admin.catalogue.types.store') }}" class="mt-4 grid gap-3 sm:grid-cols-[8rem_minmax(0,1fr)_auto] sm:items-end">
                @csrf
                <div>
                    <x-ui.label for="type_code" required>{{ __('Code') }}</x-ui.label>
                    <x-ui.input id="type_code" name="code" maxlength="32" required />
                </div>
                <div>
                    <x-ui.label for="type_name" required>{{ __('Name') }}</x-ui.label>
                    <x-ui.input id="type_name" name="name" maxlength="160" required />
                </div>
                <x-ui.button>{{ __('Add type') }}</x-ui.button>
            </form>
            <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ($enterpriseTypes as $type)
                    <li class="rounded-xl border border-slate-100 px-4 py-3 text-sm dark:border-slate-800">
                        <div class="flex items-start justify-between gap-3">
                            <span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $type->name }}</span>
                                @unless ($type->is_active)
                                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('Inactive') }}</span>
                                @endunless
                                <span class="mt-1 block text-xs text-slate-500">{{ $type->code }} · {{ $type->opportunities_count }} {{ __('opportunities') }}</span>
                            </span>
                            @if ($type->opportunities_count === 0)
                                <form method="POST" action="{{ route('admin.catalogue.types.destroy', $type) }}">@csrf @method('DELETE')<button class="shrink-0 text-sm font-semibold text-red-700 dark:text-red-300" type="submit">{{ __('Remove') }}</button></form>
                            @endif
                        </div>
                        <details class="mt-2" @if (session('editing') === $type->uuid) open @endif>
                            <summary class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-brand-700 dark:text-brand-300"><i data-lucide="pencil" class="size-3.5"></i>{{ __('Edit') }}</summary>
                            <form method="POST" action="{{ route('admin.catalogue.types.update', $type) }}" class="mt-3 grid gap-3">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_record" value="{{ $type->uuid }}">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <x-ui.label for="type-code-{{ $type->uuid }}" required>{{ __('Code') }}</x-ui.label>
                                        <x-ui.input id="type-code-{{ $type->uuid }}" name="code" maxlength="32" required :value="session('editing') === $type->uuid ? old('code', $type->code) : $type->code" />
                                    </div>
                                    <div>
                                        <x-ui.label for="type-name-{{ $type->uuid }}" required>{{ __('Name') }}</x-ui.label>
                                        <x-ui.input id="type-name-{{ $type->uuid }}" name="name" maxlength="160" required :value="session('editing') === $type->uuid ? old('name', $type->name) : $type->name" />
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label for="type-description-{{ $type->uuid }}">{{ __('Description') }}</x-ui.label>
                                    <textarea id="type-description-{{ $type->uuid }}" name="description" maxlength="500" rows="2" class="form-control">{{ session('editing') === $type->uuid ? old('description', $type->description) : $type->description }}</textarea>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" name="is_active" value="1" @checked(session('editing') === $type->uuid ? old('is_active') : $type->is_active)>
                                    {{ __('Active') }}
                                </label>
                                @if (session('editing') === $type->uuid)
                                    <x-ui.input-error for="code" />
                                    <x-ui.input-error for="name" />
                                @endif
                                <div class="sm:max-w-40"><x-ui.button>{{ __('Save type') }}</x-ui.button></div>
                            </form>
                        </details>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.workspace>
