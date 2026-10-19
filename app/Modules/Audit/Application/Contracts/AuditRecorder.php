<?php

namespace App\Modules\Audit\Application\Contracts;

use App\Modules\Audit\Application\AuditEntry;

/**
 * The only way other contexts write to the audit trail.
 */
interface AuditRecorder
{
    public function record(AuditEntry $entry): void;
}
