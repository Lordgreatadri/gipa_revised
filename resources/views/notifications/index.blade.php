<x-layouts.workspace :title="__('Notifications')">
    <div class="grid gap-3">
        @forelse ($notifications as $notice)
            <form method="POST" action="{{ auth()->user()->isStaff() ? route('admin.notifications.read', $notice) : route('investor.notifications.read', $notice) }}">
                @csrf
                <button type="submit" class="block w-full rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-card dark:border-slate-800 dark:bg-slate-900 {{ $notice->read_at ? 'opacity-70' : '' }}">
                    <span class="block font-semibold text-slate-900 dark:text-white">{{ $notice->title }}</span>
                    <span class="mt-1 block text-sm text-slate-600 dark:text-slate-300">{{ $notice->body }}</span>
                    <span class="mt-2 block text-xs text-slate-500">{{ $notice->created_at?->diffForHumans() }}</span>
                </button>
            </form>
        @empty
            <p class="text-sm text-slate-500">{{ __('No records are available yet.') }}</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</x-layouts.workspace>
