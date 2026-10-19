<?php

namespace App\Modules\Opportunity\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityTransition extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'opportunity_id',
        'from_state',
        'to_state',
        'action',
        'actor_id',
        'assigned_to',
        'reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_state' => WorkflowState::class,
            'to_state' => WorkflowState::class,
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
