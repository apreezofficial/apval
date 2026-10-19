<?php

declare(strict_types=1);

return [

    'defaults' => [
        'min_budget' => 500,
        'max_budget' => 1000000,
        'daily_spending_limit' => 200000,
        'campaign_spending_limit' => 1000000,

        'video_min_seconds' => 30,
        'video_max_seconds' => 60,

        'max_image_size_mb' => 5,
        'max_video_size_mb' => 50,
        'allowed_image_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'allowed_video_mimes' => ['mp4', 'mov'],

        'default_gateway_fee_percent' => 1.5,

        'wallet_currency' => 'NGN',

        'default_cpm_rate' => 500,
        'default_cpc_rate' => 50,
        'default_sponsored_listing_rate' => 1500,
    ],

    'media_disk' => env('ADS_MEDIA_DISK', 'public'),
];