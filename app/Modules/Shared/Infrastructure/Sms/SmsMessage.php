<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    use HasUuidRouteKey;

    public const QUEUED = 'queued';

    public const SENT = 'sent';

    public const FAILED = 'failed';

    public const SKIPPED = 'skipped';

    public const DELIVERED = 'delivered';

    public const UNDELIVERED = 'undelivered';

    /**
     * A queued or sent message with no delivery report after this long can be
     * sent again. The officer decides; nothing is resent on its own.
     */
    public const PENDING_AFTER_MINUTES = 60;

    protected $fillable = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempts' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function canBeResent(): bool
    {
        if (in_array($this->status, [self::FAILED, self::UNDELIVERED], true)) {
            return true;
        }

        if (! in_array($this->status, [self::QUEUED, self::SENT], true) || $this->delivered_at !== null) {
            return false;
        }

        $since = $this->queued_at ?? $this->created_at;

        return $since !== null && $since->lte(now()->subMinutes(self::PENDING_AFTER_MINUTES));
    }
}
