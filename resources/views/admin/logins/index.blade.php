<x-layouts.workspace :title="__('Login history')">
    <p class="mb-6 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Each sign-in, failed attempt, and lockout is kept here with the network address, browser, and location when a public address can be placed. Credentials are never stored. The audit log remains the record of other material actions.') }}</p>

    <form method="GET" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:grid-cols-3 dark:border-slate-800 dark:bg-slate-900">
        <div>
            <x-ui.label for="q">{{ __('Account') }}</x-ui.label>
            <x-ui.input id="q" name="q" :value="request('q')" maxlength="255" placeholder="{{ __('E-mail address') }}" />
        </div>
        <div>
            <x-ui.label for="outcome">{{ __('Result') }}</x-ui.label>
            <select id="outcome" name="outcome" class="form-control">
                <option value="">{{ __('All results') }}</option>
                <option value="success" @selected(request('outcome') === 'success')>{{ __('Signed in') }}</option>
                <option value="failed" @selected(request('outcome') === 'failed')>{{ __('Failed') }}</option>
                <option value="locked" @selected(request('outcome') === 'locked')>{{ __('Locked out') }}</option>
            </select>
        </div>
        <div class="flex items-end gap-3">
            <x-ui.button variant="secondary">{{ __('Filter') }}</x-ui.button>
            <a href="{{ route('admin.logins.index') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ __('Reset') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Result') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Browser') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attempts as $attempt)
                    <tr>
                        <td>{{ $attempt->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                        <td>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $attempt->email }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $attempt->guard === 'api' ? __('API') : __('Website') }}</span>
                        </td>
                        <td>{{ $attempt->outcomeLabel() }}</td>
                        <td>{{ $attempt->ip_address ?: __('Not available') }}</td>
                        <td>{{ $attempt->placeLabel() !== '' ? $attempt->placeLabel() : __('Not available') }}</td>
                        <td class="max-w-xs text-xs text-slate-500">{{ \Illuminate\Support\Str::limit((string) $attempt->user_agent, 80) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-slate-500">{{ __('No sign-in attempts are recorded yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $attempts->links() }}</div>
</x-layouts.workspace>
