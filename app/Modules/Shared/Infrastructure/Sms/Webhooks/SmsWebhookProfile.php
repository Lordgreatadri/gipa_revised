<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;

/**
 * Ignores provider health checks that carry no message correlation id.
 */
class SmsWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        foreach (['msgid', 'message_id', 'reference', 'MessageSid', 'SmsSid'] as $key) {
            if (filled($request->input($key))) {
                return true;
            }
        }

        return false;
    }
}
