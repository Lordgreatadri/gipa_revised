<?php

namespace App\Modules\Backup\Application;

use App\Modules\Backup\Domain\Models\BackupRun;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

final class BackupCatalogue
{
    public function disk(): string
    {
        return (string) config('backup.backup.destination.disks.0', 'backups');
    }

    /**
     * @return list<array{path: string, name: string, token: string, size: string, bytes: int, created_at: Carbon, verified_at: ?Carbon}>
     */
    public function archives(): array
    {
        $disk = Storage::disk($this->disk());
        $verified = BackupRun::query()
            ->whereNotNull('verified_at')
            ->pluck('verified_at', 'destination_path');
        $rows = [];

        foreach ($disk->allFiles() as $path) {
            if (! str_ends_with(strtolower($path), '.zip')) {
                continue;
            }

            $bytes = $disk->size($path);
            $rows[] = [
                'path' => $path,
                'name' => basename($path),
                'token' => ArchiveToken::encode($path),
                'size' => $this->size($bytes),
                'bytes' => $bytes,
                'created_at' => Carbon::createFromTimestamp($disk->lastModified($path)),
                'verified_at' => isset($verified[$path]) ? Carbon::parse($verified[$path]) : null,
            ];
        }

        usort($rows, fn (array $left, array $right): int => $right['created_at'] <=> $left['created_at']);

        return $rows;
    }

    public function resolve(string $token): ?string
    {
        $decoded = ArchiveToken::decode($token);

        if ($decoded === null || ! str_ends_with(strtolower($decoded), '.zip')) {
            return null;
        }

        $disk = Storage::disk($this->disk());

        if (! in_array($decoded, $disk->allFiles(), true)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @return array{path: string, size: int}|null
     */
    public function newest(): ?array
    {
        $newest = null;

        foreach ($this->archives() as $archive) {
            if ($newest === null || $archive['created_at']->greaterThan($newest['created_at'])) {
                $newest = $archive;
            }
        }

        return $newest === null ? null : ['path' => $newest['path'], 'size' => $newest['bytes']];
    }

    private function size(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }
}
