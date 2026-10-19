<?php

namespace App\Modules\Geography\Domain\Models;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Region extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [
        'code',
        'name',
        'capital',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function opportunities(): HasManyThrough
    {
        return $this->hasManyThrough(Opportunity::class, District::class);
    }
}
