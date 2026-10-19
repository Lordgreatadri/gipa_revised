<?php

return [
    'signing_key' => env('JWT_SIGNING_KEY'),
    'access_ttl_minutes' => (int) env('JWT_ACCESS_TTL_MINUTES', 15),
    'refresh_ttl_days' => (int) env('JWT_REFRESH_TTL_DAYS', 30),
];
