<x-layouts.workspace :title="__('Manage user')">
    <div class="mb-6 flex gap-2 text-sm font-semibold">
        <a href="#account" class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">{{ __('Account') }}</a>
        <a href="#security" class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">{{ __('Security') }}</a>
        <a href="#districts" class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">{{ __('Districts') }}</a>
    </div>
    <form method="POST" action="{{ route('admin.users.update', $account) }}" class="max-w-3xl">
        @csrf
        @method('PUT')

        <section id="account" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $account->name }}</h2>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('E-mail') }}</dt><dd class="mt-1">{{ $account->email }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Phone') }}</dt><dd class="mt-1">{{ $account->phone }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('E-mail verified') }}</dt><dd class="mt-1">{{ $account->email_verified_at ? __('Yes') : __('No') }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Account type') }}</dt><dd class="mt-1">{{ __('Staff') }}</dd></div>
            </dl>
        </section>

        <section id="security" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Security') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Roles decide what this person can open.') }}</p>
            <fieldset class="mt-4 space-y-2">
                <legend class="sr-only">{{ __('Staff roles') }}</legend>
                @foreach ($roles as $role)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700">
                        <input type="checkbox" name="roles[]" value="{{ $role->uuid }}" class="form-check" @checked(in_array($role->uuid, old('roles', $selected), true))>
                        <span class="font-medium text-slate-800 dark:text-slate-100">{{ $role->name }}</span>
                    </label>
                @endforeach
            </fieldset>
            <x-ui.input-error for="roles" />
        </section>

        <section id="districts" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('District transfer') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Choose the districts this person should work in. Records they already handled stay with those opportunities.') }}</p>
            <select name="districts[]" multiple class="form-control mt-4 min-h-56">
                @foreach ($regions as $region)
                    <optgroup label="{{ $region->name }}">
                        @foreach ($region->districts as $district)
                            <option value="{{ $district->uuid }}" @selected(in_array($district->uuid, old('districts', $selectedDistricts), true))>{{ $district->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <x-ui.input-error for="districts" />
        </section>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-ui.button variant="gold">{{ __('Save assignment') }}</x-ui.button>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Back to users') }}</a>
        </div>
    </form>
</x-layouts.workspace>
