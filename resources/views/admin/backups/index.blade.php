<x-layouts.workspace :title="__('Database backups')">
    <div class="mb-6 max-w-3xl">
        <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('A backup is a compressed copy of the application database. It does not include the application files or the environment file. Archives stay on the private backup disk.') }}</p>
    </div>

    @if (session('backup_error'))
        <x-ui.alert type="error" class="mb-4">{{ session('backup_error') }}</x-ui.alert>
    @endif

    @unless ($encrypted)
        <x-ui.alert type="warning" class="mb-4">{{ __('Set BACKUP_ARCHIVE_PASSWORD so each archive is encrypted. Until then, a downloaded copy is readable by anyone who obtains the file.') }}</x-ui.alert>
    @endunless

    @unless ($dumpReady)
        <x-ui.alert type="warning" class="mb-4">{{ __('pg_dump was not found. Install the PostgreSQL client tools, or set PG_DUMP_BINARY_PATH to the directory that contains pg_dump, before a backup can succeed.') }}</x-ui.alert>
    @endunless

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('Archives') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ count($archives) }}</p>
            <p class="text-sm text-slate-500">{{ $total }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('Encryption') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $encrypted ? __('On') : __('Off') }}</p>
            <p class="text-sm text-slate-500">{{ __('AES when a password is set') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('Schedule') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ __('02:15 daily') }}</p>
            <p class="text-sm text-slate-500">{{ __('Africa/Accra, when the scheduler is running') }}</p>
        </div>
    </div>

    @if ($canManage)
        <div class="mb-6 flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.backups.store') }}">
                @csrf
                <button type="submit" class="cta bg-brand-700 text-white" @disabled($openRun)>{{ $openRun ? __('Backup in progress') : __('Create database backup') }}</button>
            </form>
            <form method="POST" action="{{ route('admin.backups.retention') }}">
                @csrf
                <button type="submit" class="cta border border-slate-300 text-slate-800 dark:border-slate-700 dark:text-slate-100">{{ __('Apply retention') }}</button>
            </form>
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('Archive') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Integrity') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($archives as $archive)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">{{ $archive['name'] }}</td>
                        <td>{{ $archive['created_at']->timezone('Africa/Accra')->format('d M Y H:i') }}</td>
                        <td>{{ $archive['size'] }}</td>
                        <td>{{ $archive['verified_at'] ? __('Checked :time', ['time' => $archive['verified_at']->timezone('Africa/Accra')->format('d M Y H:i')]) : __('Not checked') }}</td>
                        <td>
                            <div class="flex flex-wrap items-start gap-2">
                                @if ($canDownload)
                                    <a class="backup-action backup-action-download" href="{{ route('admin.backups.download', $archive['token']) }}">{{ __('Download archive') }}</a>
                                @endif
                                @if ($canManage)
                                    <form method="POST" action="{{ route('admin.backups.inspect') }}">
                                        @csrf
                                        <input type="hidden" name="archive" value="{{ $archive['token'] }}">
                                        <button type="submit" class="backup-action backup-action-check">{{ __('Check archive') }}</button>
                                    </form>
                                    <details class="backup-confirm">
                                        <summary class="backup-action backup-action-delete">{{ __('Delete archive') }}</summary>
                                        <form method="POST" action="{{ route('admin.backups.destroy', $archive['token']) }}" class="mt-2 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/40">
                                            @csrf
                                            @method('DELETE')
                                            <p class="mb-2 max-w-xs text-xs leading-5 text-red-800 dark:text-red-200">{{ __('This removes the stored copy. It cannot be undone from this screen.') }}</p>
                                            <button type="submit" class="backup-action backup-action-delete-confirm">{{ __('Confirm delete') }}</button>
                                        </form>
                                    </details>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No database archives are stored yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-10 text-base font-bold text-slate-900 dark:text-white">{{ __('Recent runs') }}</h2>
    <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
        <table class="data-table min-w-full text-left">
            <thead class="bg-slate-50 dark:bg-slate-950/60">
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Requested by') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Note') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($runs as $run)
                    <tr>
                        <td>{{ $run->created_at?->timezone('Africa/Accra')->format('d M Y H:i') }}</td>
                        <td class="font-semibold">{{ ucfirst($run->status) }}</td>
                        <td>{{ $run->trigger?->name ?? __('Scheduler') }}</td>
                        <td>{{ $run->size_bytes ? number_format($run->size_bytes / 1048576, 1).' MB' : '—' }}</td>
                        <td class="max-w-xs text-sm text-slate-500">{{ $run->failure_reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No backup has been requested yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <section class="mt-10 max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Retention') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Every archive is kept for 7 days. After that, one daily archive is kept for 16 days, then one weekly archive for 8 weeks, one monthly archive for 4 months, and one yearly archive for 2 years. The newest archive is never deleted. Stored archives are also kept within 5,000 MB.') }}</p>
        <h2 class="mt-6 text-base font-bold text-slate-900 dark:text-white">{{ __('Restoration') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('This screen does not restore a backup. Restoring replaces live records and needs a planned window and a person accountable for the change.') }}</p>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
            <li>{{ __('Download the archive to an encrypted device. Record who downloaded it.') }}</li>
            <li>{{ __('Use Check archive on this page. A backup that has never been opened should not be treated as restorable.') }}</li>
            <li>{{ __('Extract the dump. If encryption is on, the archive password is BACKUP_ARCHIVE_PASSWORD for that environment.') }}</li>
            <li>{{ __('Restore with psql or pg_restore into a standby database first. Confirm a sign-in and a known record.') }}</li>
            <li>{{ __('Only then replace the live database, during a window when visitors are told the platform is unavailable.') }}</li>
        </ol>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('The daily backup runs at 02:15 Africa/Accra when the server runs the scheduler every minute. Retention runs at 03:00. A database or Redis queue also needs a worker. The sync queue finishes the dump inside the scheduler.') }}</p>
    </section>
</x-layouts.workspace>
