<?php

namespace App\Modules\Backup\Presentation\Console;

use App\Modules\Backup\Application\BackupInProgress;
use App\Modules\Backup\Application\BackupService;
use Illuminate\Console\Command;

class QueueDatabaseBackupCommand extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Queue a database backup of the application database.';

    public function handle(BackupService $backups): int
    {
        try {
            $run = $backups->queue(null);
        } catch (BackupInProgress) {
            $this->components->warn('A database backup is already queued or running.');

            return self::SUCCESS;
        }

        $this->components->info('Database backup queued: '.$run->uuid);

        return self::SUCCESS;
    }
}
