<?php

namespace App\Modules\Opportunity\Domain\Models;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\InvestmentStructure;
use App\Modules\Opportunity\Domain\Enums\ProjectStatus;
use App\Modules\Opportunity\Domain\Enums\WorkflowState;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use HasUuidRouteKey;

    /**
     * Workflow state, reference and ownership are set by the application
     * services, never from a form payload.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'objectives',
        'success_factors',
        'competitive_advantages',
        'location_description',
        'latitude',
        'longitude',
        'investment_amount',
        'currency',
        'investment_structure',
        'roi',
        'irr',
        'npv',
        'payback_period_months',
        'projected_revenue',
        'contact_name',
        'contact_organization',
        'contact_phone',
        'contact_email',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'project_status' => ProjectStatus::class,
            'workflow_state' => WorkflowState::class,
            'investment_structure' => InvestmentStructure::class,
            'investment_amount' => 'decimal:2',
            'roi' => 'decimal:4',
            'irr' => 'decimal:4',
            'npv' => 'decimal:2',
            'projected_revenue' => 'decimal:2',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function subSector(): BelongsTo
    {
        return $this->belongsTo(SubSector::class);
    }

    public function enterpriseType(): BelongsTo
    {
        return $this->belongsTo(EnterpriseType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(OpportunityTransition::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OpportunityDocument::class);
    }

    /**
     * @param  Builder<Opportunity>  $query
     */
    public function scopeInvestorVisible(Builder $query): void
    {
        $query->where('workflow_state', WorkflowState::Active->value);
    }
}
