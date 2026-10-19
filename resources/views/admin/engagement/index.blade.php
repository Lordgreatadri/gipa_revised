<x-layouts.workspace :title="__('Engagement')">
    <p class="mb-6 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Inquiries and interest from verified investors. These are separate from the opportunity workflow and stay submitted until a later review process is confirmed.') }}</p>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Investor') }}</th>
                    <th>{{ __('Opportunity') }}</th>
                    <th>{{ __('Kind') }}</th>
                    <th>{{ __('Message') }}</th>
                    <th>{{ __('Submitted') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($engagements as $engagement)
                    <tr>
                        <td class="font-semibold">{{ $engagement->reference }}</td>
                        <td>{{ $engagement->investor?->name }}</td>
                        <td>
                            @if ($engagement->opportunity)
                                @can('opportunities.view')
                                    <a class="link" href="{{ route('admin.opportunities.show', $engagement->opportunity) }}">{{ $engagement->opportunity->title }}</a>
                                @else
                                    {{ $engagement->opportunity->title }}
                                @endcan
                                <span class="mt-1 block text-xs text-slate-500">{{ $engagement->opportunity->district?->name }}</span>
                            @endif
                        </td>
                        <td>{{ $engagement->kind->label() }}</td>
                        <td class="max-w-xs">{{ $engagement->message }}</td>
                        <td>{{ $engagement->submitted_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                        <td class="text-right"><a class="link" href="{{ route('admin.engagement.show', $engagement) }}">{{ __('Messages') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $engagements->links() }}</div>
</x-layouts.workspace>
