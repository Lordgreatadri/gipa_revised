<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Response security headers (NFR-03, rules/30-security.mdc)
    |--------------------------------------------------------------------------
    |
    | Applied to every HTTP response by the SecurityHeaders middleware.
    | HSTS is only emitted on secure (HTTPS) requests.
    |
    */

    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(self), payment=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
    ],

    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', true),
        'max_age' => 31536000,
        'include_subdomains' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Script and style sources are nonce-based (Vite::useCspNonce()).
    | Theme boot is a static same-origin file (public/js/theme-boot.js);
    | layouts must not emit inline <script> or <style>.
    |
    | Alpine uses the @alpinejs/csp build so script-src does not need
    | 'unsafe-eval'. Templates bind method and property names only.
    | JavaScript never assigns element.style; Alpine x-show / x-transition
    | (which inject inline styles) are not used. Theme colour-scheme is a
    | CSS class, not an inline style. Do not add 'unsafe-inline'.
    |
    | When the Vite dev server is running (public/hot exists), its origin is
    | appended automatically so HMR works in local development only.
    |
    */

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
        'report_only' => env('SECURITY_CSP_REPORT_ONLY', false),
        'directives' => [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-ancestors' => ["'none'"],
            'form-action' => ["'self'"],
            'script-src' => ["'self'"],
            'script-src-elem' => ["'self'"],
            'style-src' => ["'self'"],
            'style-src-elem' => ["'self'"],
            'img-src' => ["'self'", 'data:', 'blob:', 'https://tiles.openfreemap.org'],
            'font-src' => ["'self'", 'data:'],
            'connect-src' => ["'self'", 'https://tiles.openfreemap.org'],
            'worker-src' => ["'self'", 'blob:'],
            'manifest-src' => ["'self'"],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication throttling
    |--------------------------------------------------------------------------
    |
    | Brute-force protection for the login form. Values are technical defaults;
    | the business lockout policy for investors is still AMB-02.
    |
    */

    'login' => [
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => env('LOGIN_DECAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password reset throttling
    |--------------------------------------------------------------------------
    |
    | "attempts,minutes" per client bucket. The link lifetime and the per-account
    | re-request cool-down live in config/auth.php (passwords.users.expire /
    | throttle). Technical defaults, not a business policy.
    |
    */

    'password_reset' => [
        'request_throttle' => env('PASSWORD_RESET_REQUEST_THROTTLE', '5,1'),
        'reset_throttle' => env('PASSWORD_RESET_RESET_THROTTLE', '5,1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Public contact form throttling
    |--------------------------------------------------------------------------
    |
    | "attempts,minutes" per IP. Matches the demo public enquiry limiter
    | (3 submissions per minute). Staff inbox handling is out of scope here.
    |
    */

    'contact' => [
        'throttle' => env('CONTACT_THROTTLE', '3,1'),
    ],

];
