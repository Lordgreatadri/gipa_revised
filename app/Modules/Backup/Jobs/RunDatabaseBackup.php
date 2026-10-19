<?php

namespace App\Modules\Backup\Jobs;

use App\Modules\Backup\Application\BackupCatalogue;
use App\Modules\Backup\Application\Contracts\DatabaseBackupRunner;
use App\Modules\Backup\Application\FailureText;
use App\Modules\Backup\Domain\Models\BackupRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RunDatabaseBackup implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public readonly int $backupRunId) {}

    public function handle(DatabaseBackupRunner $runner, BackupCatalogue $catalogue): void
    {
        $run = BackupRun::query()->find($this->backupRunId);

        if ($run === null || $run->status !== BackupRun::STATUS_QUEUED) {
            return;
        }

        $run->forceFill([
            'status' => BackupRun::STATUS_RUNNING,
            'started_at' => now(),
        ])->save();

        try {
            $runner->dump();
            $latest = $catalogue->newest();

            if ($latest === null) {
                throw new \RuntimeException('The dump finished without an archive.');
            }

            $absolute = Storage::disk($run->disk ?: $catalogue->disk())->path($latest['path']);
            $checksum = is_file($absolute) ? hash_file('sha256', $absolute) : null;

            $run->forceFill([
                'status' => BackupRun::STATUS_COMPLETED,
                'destination_path' => $latest['path'],
                'size_bytes' => $latest['size'],
                'checksum' => $checksum,
                'finished_at' => now(),
            ])->save();

            Log::channel('backups')->info('backup.completed', [
                'run' => $run->uuid,
                'archive' => basename($latest['path']),
                'bytes' => $latest['size'],
            ]);
        } catch (Throwable $exception) {
            $reason = FailureText::redact($exception->getMessage());

            $run->forceFill([
                'status' => BackupRun::STATUS_FAILED,
                'failure_reason' => $reason !== '' ? $reason : 'The database dump failed.',
                'finished_at' => now(),
            ])->save();

            Log::channel('backups')->error('backup.failed', [
                'run' => $run->uuid,
                'reason' => $run->failure_reason,
            ]);
        }
    }
}
