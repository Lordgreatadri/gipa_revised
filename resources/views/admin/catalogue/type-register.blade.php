<x-layouts.workspace :title="__('Enterprise type records')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Add or edit an enterprise type. Remove is available only when no opportunity uses it.') }}</p>
        <a href="{{ route('admin.types.index') }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Overview') }}</a>
    </div>

    <div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
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
