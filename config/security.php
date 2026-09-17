<?php

return [
    'registration_enabled' => (bool) env('REGISTRATION_ENABLED', env('APP_ENV', 'production') !== 'production'),
    'require_verified_email' => (bool) env('REQUIRE_VERIFIED_EMAIL', false),
    'trusted_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('APP_TRUSTED_HOSTS', ''))))),
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('APP_TRUSTED_PROXIES', ''))))),
];
