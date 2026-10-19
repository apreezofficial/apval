<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // 'bvn_verify' => [
    //     'url' => env('BVN_VERIFY_URL'),
    //     'api_key' => env('BVN_VERIFY_API_KEY'),
    // ],

    
    'areaconnect' => [
        'base_url' => env('AREACONNECT_BASE_URL', 'https://www.areaconnect.com.ng/api/public'),
        'api_key'  => env('AREACONNECT_API_KEY'),
        'cold_room_hourly_rate' => env('AREACONNECT_COLD_ROOM_HOURLY_RATE', 150.00),
    ],
    'agreonpay' => [
        'logistics_margin' => env('AGREONPAY_LOGISTICS_MARGIN', 0.10),
    ],
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model'   => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'system_user_id' => env('AGRINOVA_SYSTEM_USER_ID'),
    ],
    'openweathermap' => [
        'api_key' => env('OPENWEATHERMAP_API_KEY'),
    ],


    'monnify' => [
        'base_url' => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),
        'api_key' => env('MONNIFY_API_KEY'),
        'secret_key' => env('MONNIFY_SECRET_KEY'),
        'contract_code' => env('MONNIFY_CONTRACT_CODE'),
        'get_all_banks' => env('MONNIFY_GET_ALL_BANKS', true),

        // withdrawals (single transfer)
        'single_transfer_path' => env('MONNIFY_SINGLE_TRANSFER_PATH'),
        'payout_source_account_number' => env('MONNIFY_PAYOUT_SOURCE_ACCOUNT_NUMBER'),
        'resolve_account_path' => env('MONNIFY_RESOLVE_ACCOUNT_PATH'),
        'wallet_balance_path' => env('MONNIFY_WALLET_BALANCE_PATH'),
        'wallet_statement_path' => env('MONNIFY_WALLET_STATEMENT_PATH'),
    ],

];
