<?php

namespace App\Modules\InvestorEngagement\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementMessage extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(InvestorEngagement::class, 'investor_engagement_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
