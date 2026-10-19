<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Domain\Models\AssistantDocument;
use Illuminate\Support\Str;

/**
 * Builds a stable document address from the title.
 * Staff do not type it. A repeated title receives a numeric suffix.
 */
final class DocumentSlug
{
    public static function fromTitle(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit(trim($title), 80, ''));
        $base = $base !== '' ? Str::limit($base, 72, '') : 'document';
        $candidate = $base;
        $suffix = 2;

        while (self::taken($candidate, $ignoreId)) {
            $ending = '-'.$suffix;
            $candidate = Str::limit($base, 80 - strlen($ending), '').$ending;
            $suffix++;
        }

        return $candidate;
    }

    private static function taken(string $slug, ?int $ignoreId): bool
    {
        return AssistantDocument::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
