<?php

namespace App\Modules\Audit\Infrastructure\Persistence;

use App\Modules\Audit\Domain\Exceptions\AuditRecordIsImmutable;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Spatie\Activitylog\Models\Activity;

/**
 * Spatie activity model extended with the ADR-0009 columns.
 *
 * Append-only: the model refuses updates and deletes, and on PostgreSQL the
 * audit_records table carries a trigger that rejects them at the database.
 *
 * @property string $uuid
 * @property string|null $request_id
 * @property string $source
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $auth_context
 */
class AuditRecord extends Activity
{
    use HasUuidRouteKey;

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new AuditRecordIsImmutable('Audit records cannot be modified.');
        });

        static::deleting(function (): void {
            throw new AuditRecordIsImmutable('Audit records cannot be deleted.');
        });
    }
}
