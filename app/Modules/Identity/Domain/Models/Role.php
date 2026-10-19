<?php

namespace App\Modules\Identity\Domain\Models;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuidRouteKey;
}
