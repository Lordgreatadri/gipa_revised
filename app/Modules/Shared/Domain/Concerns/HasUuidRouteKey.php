<?php

namespace App\Modules\Shared\Domain\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * ADR-0003: every table keeps a bigint `id` primary key and exposes a `uuid`
 * for route model binding and every external representation. The numeric id
 * is never published.
 */
trait HasUuidRouteKey
{
    use HasUuids;

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
