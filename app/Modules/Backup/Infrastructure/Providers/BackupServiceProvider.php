<?php

namespace App\Modules\Backup\Infrastructure\Providers;

use App\Modules\Backup\Application\Contracts\DatabaseBackupRunner;
use App\Modules\Backup\Application\SpatieDatabaseBackupRunner;
use App\Modules\Backup\Presentation\Console\QueueDatabaseBackupCommand;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DatabaseBackupRunner::class, SpatieDatabaseBackupRunner::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                QueueDatabaseBackupCommand::class,
            ]);
        }
    }
}
