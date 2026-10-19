<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the PostGIS point aligned with the validated latitude and longitude.
 */
final class OpportunityLocation
{
    public function sync(Opportunity $opportunity): void
    {
        DB::update(
            'update opportunities set location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography where id = ?',
            [(float) $opportunity->longitude, (float) $opportunity->latitude, $opportunity->id],
        );
    }
}
