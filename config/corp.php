<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // For mobile apps, origin is often null/absent.
    // Keep strict if you only have web frontends.
    // If you must support mobile + unknown origins, use patterns.
    'allowed_origins' => [],

    'allowed_origins_patterns' => [
        // allow local dev web
        '^http://localhost:\d+$',
        '^http://127\.0\.0\.1:\d+$',

        // allow your web frontends if any
        '^https://([a-z0-9-]+\.)?agreonpay\.com\.ng$',
    ],

    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Request-Id'],
    'max_age' => 0,

    // For Bearer tokens, credentials are not needed.
    'supports_credentials' => false,
];
