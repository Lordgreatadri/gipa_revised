<?php

namespace App\Modules\Backup\Application;

use ZipArchive;

/**
 * Confirms an archive opens and contains a database dump.
 * It does not load that dump into the live database.
 */
final class ArchiveInspector
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function inspect(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            return ['ok' => false, 'message' => 'The archive file is not on disk.'];
        }

        $zip = new ZipArchive;
        $opened = $zip->open($absolutePath);

        if ($opened !== true) {
            return ['ok' => false, 'message' => 'The archive could not be opened.'];
        }

        $password = config('backup.backup.password');

        if (is_string($password) && $password !== '') {
            $zip->setPassword($password);
        }

        $hasDump = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);

            if (! preg_match('/\.(sql|dump)(\.gz)?$/i', $name)) {
                continue;
            }

            $sample = $zip->getFromIndex($index, 16);

            if (is_string($sample) && $sample !== '') {
                $hasDump = true;
                break;
            }
        }

        $zip->close();

        if (! $hasDump) {
            return ['ok' => false, 'message' => 'The archive opened, but it does not contain a readable database dump.'];
        }

        return ['ok' => true, 'message' => 'The archive opens and contains a database dump. This check does not restore it.'];
    }
}
