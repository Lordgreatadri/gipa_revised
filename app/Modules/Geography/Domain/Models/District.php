<?php

namespace App\Modules\Geography\Domain\Models;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [
        'region_id',
        'code',
        'name',
        'capital',
        'category',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
