<?php

namespace App\Modules\Assistant\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssistantDocument extends Model
{
    use HasUuidRouteKey;
    use SoftDeletes;

    public const AUDIENCE_PUBLIC = 'public';

    public const AUDIENCE_INVESTOR = 'investor';

    /**
     * Closed list so every document is filed the same way.
     * The keys are what is stored. The values are what staff see.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'about' => 'About the platform',
        'onboarding' => 'Registration and verification',
        'profile' => 'Business profile',
        'contact' => 'Contact and office hours',
        'catalogue' => 'Sectors, regions and districts',
        'opportunities' => 'Opportunity access',
        'engagement' => 'Inquiries and interest',
        'account' => 'Account and privacy',
        'limits' => 'What is not available yet',
    ];

    public static function categoryLabel(?string $category): string
    {
        return self::CATEGORIES[$category] ?? (string) $category;
    }

    protected $fillable = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'indexed_at' => 'datetime',
        ];
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(AssistantDocumentChunk::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
