<x-layouts.workspace :title="__('Permissions')">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-6">
            @foreach ($groups as $label => $permissions)
                @if ($permissions->isNotEmpty())
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="border-b border-slate-200 px-4 py-3 text-sm font-bold text-slate-900 dark:border-slate-800 dark:text-white">{{ $label }}</h2>
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($permissions as $permission)
                                <li class="flex items-center justify-between gap-4 px-5 py-4 text-sm">
                                    <span>
                                        <span class="block font-medium text-slate-800 dark:text-slate-100">{{ $permission->name }}</span>
                                        <span class="text-xs text-slate-500">{{ trans_choice(':count role|:count roles', $permission->roles_count, ['count' => $permission->roles_count]) }}</span>
                                    </span>
                                    @can('access.permissions.manage')
                                        @if ($permission->roles_count === 0)
                                            <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-red-700 hover:underline dark:text-red-300">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endforeach

            @if ($custom->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="border-b border-slate-200 px-4 py-3 text-sm font-bold text-slate-900 dark:border-slate-800 dark:text-white">{{ __('Custom') }}</h2>
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($custom as $permission)
                            <li class="flex items-center justify-between gap-4 px-5 py-4 text-sm">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $permission->name }}</span>
                                @can('access.permissions.manage')
                                    @if ($permission->roles_count === 0)
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-semibold text-red-700 hover:underline dark:text-red-300">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        @can('access.permissions.manage')
            <x-ui.card :title="__('Add a permission')" :description="__('Use a dotted name such as reports.export. Assign it to a role before it can be used.')">
                <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-ui.label for="name" required>{{ __('Name') }}</x-ui.label>
                        <x-ui.input name="name" :value="old('name')" required maxlength="125" placeholder="reports.export" />
                        <x-ui.input-error for="name" />
                    </div>
                    <x-ui.button variant="gold">{{ __('Create permission') }}</x-ui.button>
                </form>
            </x-ui.card>
        @endcan
    </div>
    <x-ui.input-error for="permission" class="mt-4" />
</x-layouts.workspace>
