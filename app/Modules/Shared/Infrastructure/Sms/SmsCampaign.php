<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaign extends Model
{
    use HasUuidRouteKey;

    public const DRAFT = 'draft';

    public const SENDING = 'sending';

    public const COMPLETED = 'completed';

    public const FAILED = 'failed';

    protected $fillable = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personalize' => 'boolean',
            'recipient_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(SmsCampaignRecipient::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Identity\Domain\Models\User::class, 'created_by');
    }
}
