<x-layouts.workspace :title="$role->exists ? __('Edit role') : __('New role')">
    <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="space-y-6">
        @csrf
        @if ($role->exists)
            @method('PUT')
        @endif

        <x-ui.card :title="$role->exists ? $role->name : __('New role')" :description="__('Tick the permissions this role may exercise. Saving the role stores exactly that set. Hiding a menu is not enough; the route checks the same permission.')">
            <div class="max-w-lg">
                <x-ui.label for="name" required>{{ __('Role name') }}</x-ui.label>
                <x-ui.input name="name" :value="old('name', $role->name)" required maxlength="125" :readonly="($lockedName ?? false)" />
                <x-ui.input-error for="name" />
            </div>

            <fieldset class="mt-8 space-y-6">
                <legend class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Map permissions to this role') }}</legend>
                @foreach ($permissionGroups as $label => $permissions)
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($permissions as $permission)
                                <label class="flex items-start gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check mt-0.5" @checked(in_array($permission->name, old('permissions', $selected), true))>
                                    <span class="min-w-0">
                                        <span class="block font-medium text-slate-800 dark:text-slate-100">{{ str($permission->name)->replace(['.', '-'], ' ')->title() }}</span>
                                        <span class="block text-xs text-slate-500">{{ $permission->name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </fieldset>
            <x-ui.input-error for="permissions" />
        </x-ui.card>

        @can('access.roles.manage')
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button variant="gold">{{ $role->exists ? __('Save role') : __('Create role') }}</x-ui.button>
                <a href="{{ route('admin.roles.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300">{{ __('Back to roles') }}</a>
            </div>
        @endcan
    </form>

    @if ($role->exists)
        @can('access.roles.manage')
            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="mt-4">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="secondary">{{ __('Delete role') }}</x-ui.button>
                <x-ui.input-error for="role" />
            </form>
        @endcan
    @endif
</x-layouts.workspace>
