<?php

namespace App\Modules\Shared\Infrastructure\Sms\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

/**
 * Stores the callback without the Frog shared token. The token travels in the
 * query string because Frog does not sign requests, and it must not land in
 * the webhook log.
 */
class SmsWebhookCall extends WebhookCall
{
    protected $table = 'webhook_calls';

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $data = [
            'name' => $config->name,
            'url' => $request->url(),
            'headers' => static::headersToStore($config, $request),
            'payload' => $request->except(['token']),
            'exception' => null,
        ];

        if (Schema::hasColumn((new static)->getTable(), 'attachments')) {
            $data['attachments'] = null;
        }

        return static::create($data);
    }
}
