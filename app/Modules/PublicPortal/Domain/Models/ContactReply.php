<?php

namespace App\Modules\PublicPortal\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactReply extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(ContactUs::class, 'contact_us_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
