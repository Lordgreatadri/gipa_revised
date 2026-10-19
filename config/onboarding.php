<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Investor onboarding (Client-INV-01..04, ADR-0005, ADR-0011)
    |--------------------------------------------------------------------------
    */

    'phone_verification' => [

        /*
        | Delivery driver for phone verification codes. The domain only depends
        | on the PhoneVerificationSender contract.
        |
        |  sms   - the administrator's active SMS gateways, in priority order.
        |          Frog, Twilio, Log only, and any later catalogue entry are
        |          used without a code change. Each gateway gets the number in
        |          its own phone_format. Log only is the local backup: it
        |          records the message and does not call a network.
        |  array - in-memory, for automated tests.
        |  mail  - non-production only: delivers the code to the investor's
        |          e-mail. Refuses to run in production.
        |  null  - no delivery.
        */
        'driver' => env('PHONE_VERIFICATION_DRIVER', 'sms'),

        // Technical defaults. Business retry/expiry/lockout policy is AMB-02.
        'code_length' => 6,
        'ttl_minutes' => env('PHONE_VERIFICATION_TTL_MINUTES', 10),

        // Route throttles: "<attempts>,<minutes>".
        'send_throttle' => env('PHONE_VERIFICATION_SEND_THROTTLE', '3,10'),
        'confirm_throttle' => env('PHONE_VERIFICATION_CONFIRM_THROTTLE', '5,1'),
    ],

    'email_verification' => [
        'resend_throttle' => env('EMAIL_VERIFICATION_RESEND_THROTTLE', '6,1'),
    ],

    'registration' => [
        'throttle' => env('REGISTRATION_THROTTLE', '10,1'),
    ],

];
