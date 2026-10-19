<?php

namespace App\Modules\InvestorEngagement\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementKind;
use App\Modules\InvestorEngagement\Domain\Enums\EngagementState;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestorEngagement extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => EngagementKind::class,
            'state' => EngagementState::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EngagementMessage::class);
    }
}
