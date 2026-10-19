<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks;

final class SecureCompare
{
    public static function equals(string $known, string $given): bool
    {
        if ($known === '' || strlen($known) !== strlen($given)) {
            return false;
        }

        return hash_equals($known, $given);
    }
}
