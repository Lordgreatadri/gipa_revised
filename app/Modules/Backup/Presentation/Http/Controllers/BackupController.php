<?php

namespace App\Modules\Backup\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Backup\Application\ArchiveInspector;
use App\Modules\Backup\Application\BackupCatalogue;
use App\Modules\Backup\Application\BackupInProgress;
use App\Modules\Backup\Application\BackupService;
use App\Modules\Backup\Application\DumpClient;
use App\Modules\Backup\Domain\Models\BackupRun;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController
{
    public function __construct(
        private readonly BackupCatalogue $catalogue,
        private readonly BackupService $backups,
        private readonly ArchiveInspector $inspector,
        private readonly DumpClient $dumpClient,
        private readonly AuditRecorder $audit,
    ) {}

    public function index(Request $request): View
    {
        $archives = $this->catalogue->archives();

        return view('admin.backups.index', [
            'archives' => $archives,
            'runs' => BackupRun::query()->with('trigger')->latest('id')->limit(20)->get(),
            'openRun' => $this->backups->openRun(),
            'encrypted' => filled(config('backup.backup.password')),
            'dumpReady' => $this->dumpClient->available(),
            'total' => $this->size(array_sum(array_column($archives, 'bytes'))),
            'canManage' => $request->user()?->can(AccessPermissions::BACKUPS_MANAGE) ?? false,
            'canDownload' => $request->user()?->can(AccessPermissions::BACKUPS_DOWNLOAD) ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $run = $this->backups->queue($request->user()?->id);
        } catch (BackupInProgress $exception) {
            return back()->with('backup_error', $exception->getMessage());
        }

        $this->audit->record(
            AuditEntry::for('backup.queued', $run)
                ->causedBy($request->user())
                ->withMetadata(['run' => $run->uuid]),
        );

        return back()->with('status', __('Database backup queued. Refresh this page to see the result.'));
    }

    public function download(Request $request, string $archive): StreamedResponse
    {
        $path = $this->catalogue->resolve($archive);
        abort_unless($path !== null, 404);

        $this->audit->record(
            AuditEntry::for('backup.downloaded')
                ->causedBy($request->user())
                ->withMetadata(['archive' => basename($path)]),
        );

        Log::channel('backups')->info('backup.downloaded', [
            'user' => $request->user()?->id,
            'archive' => basename($path),
        ]);

        return Storage::disk($this->catalogue->disk())->download($path);
    }

    public function destroy(Request $request, string $archive): RedirectResponse
    {
        $path = $this->catalogue->resolve($archive);
        abort_unless($path !== null, 404);

        Storage::disk($this->catalogue->disk())->delete($path);

        $this->audit->record(
            AuditEntry::for('backup.deleted')
                ->causedBy($request->user())
                ->withMetadata(['archive' => basename($path)]),
        );

        return back()->with('status', __('Backup archive deleted.'));
    }

    public function retention(Request $request): RedirectResponse
    {
        try {
            $this->backups->applyRetention();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('backup_error', __('Retention could not be applied. The backup log has the failure.'));
        }

        $this->audit->record(
            AuditEntry::for('backup.retention_applied')->causedBy($request->user()),
        );

        return back()->with('status', __('Retention applied. The newest archive is kept.'));
    }

    public function inspect(Request $request): RedirectResponse
    {
        $path = $this->catalogue->resolve((string) $request->string('archive'));
        abort_unless($path !== null, 404);

        $absolute = Storage::disk($this->catalogue->disk())->path($path);
        $result = $this->inspector->inspect($absolute);

        if ($result['ok']) {
            BackupRun::query()
                ->where('destination_path', $path)
                ->where('status', BackupRun::STATUS_COMPLETED)
                ->update(['verified_at' => now()]);
        }

        $this->audit->record(
            AuditEntry::for('backup.inspected')
                ->causedBy($request->user())
                ->withMetadata([
                    'archive' => basename($path),
                    'ok' => $result['ok'],
                ]),
        );

        return back()->with($result['ok'] ? 'status' : 'backup_error', $result['message']);
    }

    private function size(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }
}
