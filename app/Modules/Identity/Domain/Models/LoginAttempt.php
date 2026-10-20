<?php

namespace App\Modules\Identity\Domain\Models;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    use HasUuidRouteKey;

    public const OUTCOME_SUCCESS = 'success';

    public const OUTCOME_FAILED = 'failed';

    public const OUTCOME_LOCKED = 'locked';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outcomeLabel(): string
    {
        return match ($this->outcome) {
            self::OUTCOME_SUCCESS => 'Signed in',
            self::OUTCOME_LOCKED => 'Locked out',
            default => 'Failed',
        };
    }

    public function placeLabel(): string
    {
        $parts = array_values(array_filter([
            $this->city_name,
            $this->region_name,
            $this->country_name,
        ], fn ($part) => is_string($part) && $part !== ''));

        return implode(', ', $parts);
    }
}
