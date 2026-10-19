<?php

/*
 * Delivery-report callbacks. The signing material is the encrypted provider
 * record, resolved when the request arrives. It is never read from this file,
 * so config:cache cannot freeze a credential.
 */

return [
    'configs' => [
        [
            'name' => 'frog-sms',
            'signing_secret' => '',
            'signature_header_name' => 'X-Frog-Signature',
            'signature_validator' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\SignatureValidators\FrogSmsSignatureValidator::class,
            'webhook_profile' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\SmsWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\SmsWebhookCall::class,
            'store_headers' => ['User-Agent', 'Content-Type'],
            'store_attachments' => false,
            'process_webhook_job' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\Jobs\ProcessSmsStatusWebhookJob::class,
        ],
        [
            'name' => 'twilio-sms',
            'signing_secret' => '',
            'signature_header_name' => 'X-Twilio-Signature',
            'signature_validator' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\SignatureValidators\TwilioSmsSignatureValidator::class,
            'webhook_profile' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\SmsWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\SmsWebhookCall::class,
            'store_headers' => ['User-Agent', 'Content-Type'],
            'store_attachments' => false,
            'process_webhook_job' => \App\Modules\Shared\Infrastructure\Sms\Webhooks\Jobs\ProcessSmsStatusWebhookJob::class,
        ],
    ],

    'delete_after_days' => 30,

    'add_unique_token_to_route_name' => false,
];
