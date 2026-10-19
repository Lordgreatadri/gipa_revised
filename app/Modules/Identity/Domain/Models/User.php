<?php

namespace App\Modules\Identity\Domain\Models;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Infrastructure\Notifications\ResetPasswordLink;
use App\Modules\Identity\Infrastructure\Notifications\VerifyEmailAddress;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use App\Modules\Shared\Domain\Models\Country;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $phone E.164
 * @property Carbon|null $phone_verified_at
 * @property UserType $user_type
 * @property int|null $country_id
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuidRouteKey, Notifiable;

    /**
     * user_type is deliberately not fillable: it is set explicitly by the
     * registration / invitation use case, never from request input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'country_id',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'staff_districts')->withTimestamps();
    }

    public function investorProfile(): HasOne
    {
        return $this->hasOne(InvestorProfile::class);
    }

    public function isInvestor(): bool
    {
        return $this->user_type === UserType::Investor;
    }

    public function isStaff(): bool
    {
        return $this->user_type === UserType::Staff;
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Replaces the framework notification so the link carries the uuid,
     * not the numeric id (ADR-0003), and is delivered through the queue.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailAddress);
    }

    /**
     * Queued delivery of the password-reset link.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordLink($token));
    }
}
