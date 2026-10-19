<?php

use App\Modules\Shared\Infrastructure\Sms\Providers\FrogSmsProvider;
use App\Modules\Shared\Infrastructure\Sms\Providers\LogSmsProvider;
use App\Modules\Shared\Infrastructure\Sms\Providers\TwilioSmsProvider;

return [

    /*
    | Providers are declared here so a future gateway is one adapter plus one
    | catalogue entry. Credentials are captured by an administrator and stored
    | encrypted. They are not read from the environment.
    */

    'providers' => [

        'frog' => [
            'label' => 'Wigal Frog',
            'adapter' => FrogSmsProvider::class,
            'phone_format' => 'digits',
            'supports_balance' => true,
            'supports_bundles' => true,
            'supports_delivery_callbacks' => true,
            'webhook_config' => 'frog-sms',
            'callback' => [
                'id_fields' => ['msgid', 'message_id', 'reference', 'data.msgid'],
                'status_fields' => ['status', 'delivery_status'],
                'detail_fields' => ['reason', 'message'],
                'occurred_field' => 'statusdate',
                'statuses' => [
                    'queued' => ['accepted', 'acceptd', 'queued', 'waiting', 'sending', 'processing', 'scheduled'],
                    'sent' => ['sent'],
                    'delivered' => ['delivered', 'delivrd', 'answered', 'success'],
                    'undelivered' => ['undeliv', 'undelivered', 'expired', 'rejectd', 'rejected', 'busy'],
                    'failed' => ['failed', 'fail', 'error', 'deleted'],
                ],
            ],
            'docs_url' => 'https://frogdocs.wigal.com.gh/send_general.html',
            'defaults' => [
                'base_url' => 'https://frogapi.wigal.com.gh/api/v3',
            ],
            'credentials' => [
                'username' => ['label' => 'Username', 'secret' => false, 'required' => true, 'hint' => 'Frog account username issued by Wigal.'],
                'api_key' => ['label' => 'API key', 'secret' => true, 'required' => true, 'hint' => 'Sent as the API-KEY header. Leave blank to keep the stored key.'],
                'base_url' => ['label' => 'API base URL', 'secret' => false, 'required' => false, 'hint' => 'Leave blank to use https://frogapi.wigal.com.gh/api/v3. A host-only value is completed to that path. The website host frog.wigal.com.gh is not the API.'],
            ],
        ],

        'twilio' => [
            'label' => 'Twilio',
            'adapter' => TwilioSmsProvider::class,
            'phone_format' => 'e164',
            'supports_balance' => true,
            'supports_bundles' => false,
            'supports_delivery_callbacks' => true,
            'webhook_config' => 'twilio-sms',
            'callback' => [
                'id_fields' => ['MessageSid', 'SmsSid', 'sid'],
                'status_fields' => ['MessageStatus', 'SmsStatus'],
                'detail_fields' => ['ErrorMessage'],
                'occurred_field' => 'Timestamp',
                'statuses' => [
                    'queued' => ['accepted', 'queued', 'sending', 'scheduled'],
                    'sent' => ['sent'],
                    'delivered' => ['delivered'],
                    'undelivered' => ['undelivered'],
                    'failed' => ['failed'],
                ],
            ],
            'docs_url' => 'https://www.twilio.com/docs/messaging/api',
            'defaults' => [
                'base_url' => 'https://api.twilio.com/2010-04-01',
            ],
            'credentials' => [
                'account_sid' => ['label' => 'Account SID', 'secret' => false, 'required' => true, 'hint' => 'Starts with AC.'],
                'auth_token' => ['label' => 'Auth token', 'secret' => true, 'required' => true, 'hint' => 'Leave blank to keep the stored token. Twilio also signs delivery reports with this token.'],
                'base_url' => ['label' => 'API base URL', 'secret' => false, 'required' => false, 'hint' => 'Leave blank unless using a regional Twilio edge.'],
            ],
        ],

        'log' => [
            'label' => 'Log only',
            'adapter' => LogSmsProvider::class,
            'phone_format' => 'e164',
            'supports_balance' => false,
            'supports_bundles' => false,
            'supports_delivery_callbacks' => false,
            'webhook_config' => null,
            'docs_url' => null,
            'defaults' => [],
            'credentials' => [],
        ],

    ],

    'queue' => env('SMS_QUEUE', 'default'),

    'http' => [
        'timeout' => 12,
        'connect_timeout' => 5,
    ],

];
