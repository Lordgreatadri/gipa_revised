<?php

namespace App\Modules\PublicPortal\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ContactUs extends Model
{
    use HasUuidRouteKey, Notifiable, SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_RESPONDED = 'responded';

    public const STATUS_CLOSED = 'closed';

    protected $table = 'contact_us';

    /**
     * Workflow columns stay off fillable so a public request cannot assign them.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference',
        'name',
        'email',
        'phone_number',
        'available_time_for_call',
        'subject',
        'message',
        'ip_address',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'responded_at' => 'datetime',
            'sms_sent' => 'boolean',
            'sms_sent_at' => 'datetime',
            'email_sent' => 'boolean',
            'email_sent_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactReply::class);
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
