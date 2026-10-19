<?php

namespace App\Modules\Backup\Application;

use Illuminate\Support\Facades\Process;
use Throwable;

final class DumpClient
{
    public function available(): bool
    {
        $connection = (string) config('database.default');
        $directory = (string) config("database.connections.{$connection}.dump.dump_binary_path");
        $binary = PHP_OS_FAMILY === 'Windows' ? 'pg_dump.exe' : 'pg_dump';

        if ($directory !== '') {
            return is_file($directory.DIRECTORY_SEPARATOR.$binary);
        }

        try {
            $command = PHP_OS_FAMILY === 'Windows' ? 'where pg_dump' : 'command -v pg_dump';

            return Process::timeout(5)->run($command)->successful();
        } catch (Throwable) {
            return false;
        }
    }
}
