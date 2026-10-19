<?php

namespace App\Modules\Backup\Application\Contracts;

interface DatabaseBackupRunner
{
    public function dump(): void;

    public function applyRetention(): void;
}
