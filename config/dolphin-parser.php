<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dolphin Parser API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Dolphin PDF Parser RunPod endpoint credentials here.
    |
    */

    // RunPod API endpoint (required)
    // Format: https://api.runpod.ai/v2/YOUR_ENDPOINT_ID
    'endpoint' => env('DOLPHIN_PARSER_ENDPOINT', ''),

    // RunPod API key (required)
    // Get this from RunPod dashboard: Settings > API Keys
    'api_key' => env('DOLPHIN_PARSER_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Request Defaults
    |--------------------------------------------------------------------------
    */

    // Default labels to exclude from parsing
    'excluded_labels' => explode(',', env('DOLPHIN_PARSER_EXCLUDED_LABELS', 'foot,header')),

    // Default tags to exclude from parsing
    'excluded_tags' => explode(',', env('DOLPHIN_PARSER_EXCLUDED_TAGS', 'author,meta_pub_date')),

    /*
    |--------------------------------------------------------------------------
    | Callback Configuration
    |--------------------------------------------------------------------------
    |
    | If set, the parser will POST results to this URL when complete.
    | This is useful for async processing with webhooks.
    |
    */

    'callback_url' => env('DOLPHIN_PARSER_CALLBACK_URL', null),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */

    // Request timeout in seconds
    'timeout' => env('DOLPHIN_PARSER_TIMEOUT', 300),

    // Retry attempts for failed requests
    'retries' => env('DOLPHIN_PARSER_RETRIES', 3),

    // Delay between retries (seconds)
    'retry_delay' => env('DOLPHIN_PARSER_RETRY_DELAY', 2),

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Where to store downloaded ZIP files from the storage server.
    |
    */

    // Storage disk for downloaded files
    'storage_disk' => env('DOLPHIN_PARSER_STORAGE_DISK', 'local'),

    // Storage path within the disk
    'storage_path' => env('DOLPHIN_PARSER_STORAGE_PATH', 'dolphin-parser'),

    /*
    |--------------------------------------------------------------------------
    | Storage Server (Optional)
    |--------------------------------------------------------------------------
    |
    | If you're using a separate storage server for results.
    |
    */

    'storage_server' => [
        'endpoint' => env('DOLPHIN_STORAGE_ENDPOINT', ''),
        'api_key' => env('DOLPHIN_STORAGE_API_KEY', ''),
    ],
];
