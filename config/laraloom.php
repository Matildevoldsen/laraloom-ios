<?php

return [
    'api_url' => env('LARALOOM_API_URL', 'https://laraloom-production-sbummi.laravel.cloud/api/v1'),

    'realtime' => [
        'key' => env('WEBSOCKETS_APP_KEY', 'laraloom-b2yknl'),
        'host' => env('WEBSOCKETS_HOST', 'wss.vask.dev'),
        'port' => (int) env('WEBSOCKETS_PORT', 443),
    ],
];
