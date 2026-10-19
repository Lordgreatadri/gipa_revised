<?php

namespace App\Modules\InvestorOnboarding\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Business profile for an investor account. The user record stays the login.
 * KYC documents are not collected here; the client has not confirmed them.
 *
 * @property ProfileType $profile_type
 * @property ProfileOnboardingState $onboarding_state
 * @property ProfileStatus $status
 * @property Carbon|null $last_engaged_at
 * @property Carbon|null $deleted_at
 */
class InvestorProfile extends Model
{
    use HasUuidRouteKey, SoftDeletes;

    protected $fillable = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profile_type' => ProfileType::class,
            'onboarding_state' => ProfileOnboardingState::class,
            'status' => ProfileStatus::class,
            'last_engaged_at' => 'datetime',
            'consent_to_delete' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canEngage(): bool
    {
        return $this->onboarding_state === ProfileOnboardingState::Approved
            && $this->status === ProfileStatus::Active;
    }
}
