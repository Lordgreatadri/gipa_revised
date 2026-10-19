<?php

namespace App\Modules\Backup\Application;

use App\Modules\Backup\Application\Contracts\DatabaseBackupRunner;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

/**
 * Database dumps go through spatie/laravel-backup. The application tree is
 * not included. Artisan's console text is not returned, because a dump
 * failure can echo a connection string.
 */
final class SpatieDatabaseBackupRunner implements DatabaseBackupRunner
{
    public function dump(): void
    {
        $this->exposeDumpClient();

        $exitCode = Artisan::call('backup:run', [
            '--only-db' => true,
            '--disable-notifications' => true,
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException('The database dump did not finish.');
        }
    }

    public function applyRetention(): void
    {
        $exitCode = Artisan::call('backup:clean', [
            '--disable-notifications' => true,
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException('Retention could not be applied.');
        }
    }

    /**
     * pg_dump is often installed but not on PATH. Windows also needs the
     * .exe when the binary is named in full, so the directory is placed on
     * PATH and the dump command stays "pg_dump".
     */
    private function exposeDumpClient(): void
    {
        $connection = (string) config('database.default');
        $directory = (string) config("database.connections.{$connection}.dump.dump_binary_path");

        if ($directory === '' || ! is_dir($directory)) {
            return;
        }

        $current = (string) getenv('PATH');
        putenv('PATH='.$directory.PATH_SEPARATOR.$current);
        config(["database.connections.{$connection}.dump.dump_binary_path" => '']);
    }
}
