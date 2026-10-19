<x-layouts.workspace :title="__('Roles')">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-slate-600 dark:text-slate-300">{{ __('Roles bundle permissions. Changing a role changes what every assigned account can do. The Super Administrator role cannot be deleted.') }}</p>
        @can('access.roles.manage')
            <a href="{{ route('admin.roles.create') }}" class="cta bg-gold-500 text-brand-950 hover:bg-gold-400">
                <i data-lucide="plus" class="size-4"></i>
                {{ __('New role') }}
            </a>
        @endcan
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('Role') }}</th>
                    <th>{{ __('Permissions') }}</th>
                    <th>{{ __('Users') }}</th>
                    <th><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">{{ $role->name }}</td>
                        <td>{{ $role->permissions_count }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="link">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.workspace>
