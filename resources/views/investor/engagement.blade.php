<x-layouts.workspace :title="__('My engagement')">
    <p class="mb-6 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Inquiries and expressions of interest you have submitted. Review steps beyond submission are recorded when GIPA confirms them.') }}</p>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Opportunity') }}</th>
                    <th>{{ __('Kind') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($engagements as $engagement)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">{{ $engagement->reference }}</td>
                        <td>
                            @if ($engagement->opportunity)
                                <a class="link" href="{{ route('investor.opportunities.show', $engagement->opportunity) }}">{{ $engagement->opportunity->title }}</a>
                            @endif
                        </td>
                        <td>{{ $engagement->kind->label() }}</td>
                        <td>{{ $engagement->state->label() }}</td>
                        <td class="text-right"><a class="link" href="{{ route('investor.engagement.show', $engagement) }}">{{ __('Messages') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $engagements->links() }}</div>
</x-layouts.workspace>
