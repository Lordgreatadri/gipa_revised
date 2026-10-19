<x-layouts.workspace :title="__('Users')">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('People') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-slate-300">{{ __('Invite staff, move them between districts, and export the directory. Investors keep the role they received at registration.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.export', ['format' => 'csv', 'q' => $term]) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-700">CSV</a>
            <a href="{{ route('admin.users.export', ['format' => 'xls', 'q' => $term]) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-700">Excel</a>
            <a href="{{ route('admin.users.export', ['format' => 'pdf', 'q' => $term]) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-700">PDF</a>
        </div>
    </div>

    <form method="GET" class="mb-4 flex max-w-xl gap-2">
        <input type="search" name="q" value="{{ $term }}" placeholder="{{ __('Search name or e-mail') }}" class="form-control">
        <x-ui.button>{{ __('Search') }}</x-ui.button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('Person') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Roles') }}</th>
                    <th>{{ __('Districts') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $account)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex size-10 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-800 dark:bg-brand-950 dark:text-brand-100">{{ strtoupper(substr($account->name, 0, 1)) }}</span>
                                <span>
                                    <span class="block font-semibold text-slate-900 dark:text-white">{{ $account->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $account->email }}</span>
                                </span>
                            </div>
                        </td>
                        <td>{{ $account->isStaff() ? __('Staff') : __('Investor') }}</td>
                        <td>{{ $account->roles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="text-sm text-slate-600">{{ $account->districts->pluck('code')->join(', ') ?: '—' }}</td>
                        <td class="text-right">
                            @can('access.users.assign-roles')
                                @if ($account->isStaff())
                                    <a href="{{ route('admin.users.edit', $account) }}" class="link">{{ __('Manage') }}</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>

    @can('access.users.assign-roles')
        <form method="POST" action="{{ route('admin.users.invite') }}" class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Invite staff') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('They receive a link to choose a password. Assign the district they will work in. Moving someone later is done from their record.') }}</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <x-ui.label for="invite_name" required>{{ __('Name') }}</x-ui.label>
                    <x-ui.input id="invite_name" name="name" :value="old('name')" required />
                    <x-ui.input-error for="name" />
                </div>
                <div>
                    <x-ui.label for="invite_email" required>{{ __('E-mail') }}</x-ui.label>
                    <x-ui.input id="invite_email" name="email" type="email" :value="old('email')" required />
                    <x-ui.input-error for="email" />
                </div>
                <div>
                    <x-ui.label for="invite_phone" required>{{ __('Phone') }}</x-ui.label>
                    <x-ui.input id="invite_phone" name="phone" :value="old('phone')" placeholder="+233201234567" required />
                    <x-ui.input-error for="phone" />
                </div>
                <div>
                    <x-ui.label for="invite_role" required>{{ __('Role') }}</x-ui.label>
                    <select id="invite_role" name="role" class="form-control" required>
                        <option value="">{{ __('Select a role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->uuid }}" @selected(old('role') === $role->uuid)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <x-ui.input-error for="role" />
                </div>
                <div class="md:col-span-2">
                    <x-ui.label for="invite_districts">{{ __('District assignment') }}</x-ui.label>
                    <select id="invite_districts" name="districts[]" multiple class="form-control min-h-40">
                        @foreach ($regions as $region)
                            <optgroup label="{{ $region->name }}">
                                @foreach ($region->districts as $district)
                                    <option value="{{ $district->uuid }}">{{ $district->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>
            <x-ui.button class="mt-5" variant="gold">{{ __('Send invitation') }}</x-ui.button>
        </form>
    @endcan
</x-layouts.workspace>
