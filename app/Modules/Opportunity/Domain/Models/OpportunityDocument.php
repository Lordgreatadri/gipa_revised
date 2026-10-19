<?php

namespace App\Modules\Opportunity\Domain\Models;

use App\Modules\Opportunity\Domain\Enums\DocumentCategory;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;

class OpportunityDocument extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [
        'opportunity_id',
        'category',
        'original_name',
        'storage_path',
        'mime_type',
        'size',
        'checksum',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
        ];
    }
}
