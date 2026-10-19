<x-layouts.workspace :title="__('Assistant knowledge')">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('GIPA Assistant') }}</p>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Published documents are the only narrative the assistant may quote. Live tools still apply the same access rules as the site. Certificates and matchmaking are not part of this knowledge.') }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.assistant.knowledge.reindex') }}">
                @csrf
                <x-ui.button variant="ghost">{{ __('Index again') }}</x-ui.button>
            </form>
            <a href="{{ route('admin.assistant.knowledge.create') }}" class="cta bg-brand-800 text-white hover:bg-brand-700">{{ __('Add document') }}</a>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th>{{ __('Document') }}</th>
                    <th>{{ __('Audience') }}</th>
                    <th>{{ __('Published') }}</th>
                    <th>{{ __('Indexed') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $document->title }}</p>
                            <p class="text-xs text-slate-500">{{ \App\Modules\Assistant\Domain\Models\AssistantDocument::categoryLabel($document->category) }}</p>
                        </td>
                        <td>{{ $document->audience === 'investor' ? __('Investor') : __('Public') }}</td>
                        <td>{{ $document->is_published ? __('Yes') : __('No') }}</td>
                        <td>{{ $document->indexed_at?->timezone('Africa/Accra')->format('d M Y H:i') ?? __('Not indexed') }}</td>
                        <td class="text-right"><a class="link" href="{{ route('admin.assistant.knowledge.edit', $document) }}">{{ __('Edit') }}</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">{{ __('No assistant documents yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $documents->links() }}</div>
</x-layouts.workspace>
