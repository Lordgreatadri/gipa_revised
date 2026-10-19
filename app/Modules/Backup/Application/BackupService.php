<?php

namespace App\Modules\Backup\Application;

use App\Modules\Backup\Application\Contracts\DatabaseBackupRunner;
use App\Modules\Backup\Domain\Models\BackupRun;
use App\Modules\Backup\Jobs\RunDatabaseBackup;
use Illuminate\Support\Facades\Bus;

final class BackupService
{
    public function __construct(
        private readonly DatabaseBackupRunner $runner,
        private readonly BackupCatalogue $catalogue,
    ) {}

    public function queue(?int $triggeredBy): BackupRun
    {
        if ($this->openRun() !== null) {
            throw new BackupInProgress('A database backup is already queued or running.');
        }

        $run = new BackupRun;
        $run->forceFill([
            'type' => BackupRun::TYPE_DATABASE,
            'status' => BackupRun::STATUS_QUEUED,
            'disk' => $this->catalogue->disk(),
            'triggered_by' => $triggeredBy,
        ])->save();

        $job = new RunDatabaseBackup($run->id);

        if (config('queue.default') === 'sync' && ! app()->runningInConsole()) {
            Bus::dispatchAfterResponse($job);
        } else {
            Bus::dispatch($job);
        }

        return $run;
    }

    public function applyRetention(): void
    {
        $this->runner->applyRetention();
    }

    public function openRun(): ?BackupRun
    {
        return BackupRun::query()
            ->whereIn('status', BackupRun::OPEN_STATUSES)
            ->latest('id')
            ->first();
    }
}
