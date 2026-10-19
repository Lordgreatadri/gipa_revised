<x-layouts.workspace :title="__('Contact enquiries')">
    <div class="mb-4 flex flex-wrap gap-2 text-sm font-semibold">
        @foreach (['' => __('All'), 'new' => __('New'), 'read' => __('Read'), 'responded' => __('Responded'), 'closed' => __('Closed')] as $value => $label)
            <a href="{{ route('admin.contact', ['status' => $value]) }}" @class(['rounded-full px-3 py-1', 'bg-brand-700 text-white' => $status === $value, 'bg-white text-slate-700 dark:bg-slate-900 dark:text-slate-200' => $status !== $value])>{{ $label }}</a>
        @endforeach
    </div>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60"><tr><th>{{ __('Reference') }}</th><th>{{ __('From') }}</th><th>{{ __('Subject') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($enquiries as $enquiry)
                    <tr>
                        <td class="font-semibold">{{ $enquiry->reference }}</td>
                        <td>{{ $enquiry->name }}<span class="block text-xs text-slate-500">{{ $enquiry->email }}</span></td>
                        <td>{{ $enquiry->subject }}</td>
                        <td>{{ ucfirst($enquiry->status) }}</td>
                        <td class="text-right"><a class="link" href="{{ route('admin.contact.show', $enquiry) }}">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No records are available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $enquiries->links() }}</div>
</x-layouts.workspace>
