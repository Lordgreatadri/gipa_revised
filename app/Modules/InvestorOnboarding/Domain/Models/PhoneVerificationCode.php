<?php

namespace App\Modules\InvestorOnboarding\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A one-time phone verification challenge. Only the hash of the code is
 * stored; the plaintext exists solely in the delivery message.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $phone
 * @property string $code_hash
 * @property int $failed_attempts
 * @property Carbon $expires_at
 * @property Carbon|null $consumed_at
 */
class PhoneVerificationCode extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [
        'phone',
        'code_hash',
        'expires_at',
    ];

    protected $hidden = [
        'code_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'failed_attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<PhoneVerificationCode>  $query
     * @return Builder<PhoneVerificationCode>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('consumed_at')->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }
}
