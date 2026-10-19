<?php

namespace App\Modules\Backup\Application;

use Illuminate\Support\Str;

final class FailureText
{
    public static function redact(string $message): string
    {
        $message = preg_replace('/PGPASSWORD=\S+/i', 'PGPASSWORD=[redacted]', $message) ?? $message;
        $message = preg_replace('/(--password(?:=|\s+))\S+/i', '$1[redacted]', $message) ?? $message;
        $message = preg_replace('/(password\s*[=:]\s*)\S+/i', '$1[redacted]', $message) ?? $message;

        return Str::limit(trim($message), 500, '');
    }
}
