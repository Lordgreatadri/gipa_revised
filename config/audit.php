<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit (ADR-0009)
    |--------------------------------------------------------------------------
    |
    | The audit store is spatie/laravel-activitylog (decision D-5), extended
    | with IOMP columns: uuid, request_id, source, ip_address, user_agent and
    | auth_context. Records are append-only; see the audit_records trigger.
    |
    */

    'log_name' => 'iomp',

    /*
    | Property keys removed from before/after/metadata payloads before an audit
    | record is written. Matching is case-insensitive on the key name and is
    | applied recursively. Never rely on callers to strip secrets.
    */

    'redacted_keys' => [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'auth_token',
        'webhook_secret',
        'secret',
        'code',
        'otp',
        'code_hash',
        'verification_code',
        'private_key',
        'authorization',
        'cookie',
    ],

    'redaction_marker' => '[REDACTED]',

    /*
    | Retention is configurable per ADR-0009 but nothing deletes audit records
    | automatically. null means "no automatic deletion". A retention policy
    | (AMB-26) must be approved before any clean-up job is scheduled.
    */

    'retention_days' => env('AUDIT_RETENTION_DAYS'),

];
