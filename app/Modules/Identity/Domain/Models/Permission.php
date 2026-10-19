<?php

namespace App\Modules\Identity\Domain\Models;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasUuidRouteKey;
}
