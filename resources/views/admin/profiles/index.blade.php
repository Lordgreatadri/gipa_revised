<x-layouts.workspace :title="__('Investor profiles')">
    <p class="mb-6 max-w-2xl text-sm text-slate-600 dark:text-slate-300">{{ __('Approval lets the investor inquire and express interest. Document checks stay out until GIPA confirms the KYC set.') }}</p>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60"><tr><th>{{ __('Organization') }}</th><th>{{ __('Investor') }}</th><th>{{ __('Type') }}</th><th>{{ __('Review') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($profiles as $profile)
                    <tr>
                        <td class="font-semibold">{{ $profile->organization_name }}<span class="block text-xs font-normal text-slate-500">{{ $profile->country_code }} · {{ $profile->currency }}</span></td>
                        <td>{{ $profile->user?->name }}<span class="block text-xs text-slate-500">{{ $profile->business_email }}</span></td>
                        <td>{{ $profile->profile_type->label() }}</td>
                        <td>{{ $profile->onboarding_state->label() }}</td>
                        <td class="text-right">
                            @if ($profile->onboarding_state->value !== 'approved')
                                <form method="POST" action="{{ route('admin.profiles.approve', $profile) }}">
                                    @csrf
                                    <button type="submit" class="link">{{ __('Approve') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $profiles->links() }}</div>
</x-layouts.workspace>
