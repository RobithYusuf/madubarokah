<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ngrok Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ngrok tunneling service
    |
    */

    // Set your ngrok URL here when using ngrok
    'url' => env('NGROK_URL', null),
    
    // Automatically trust ngrok domains
    'trusted_proxies' => [
        '*.ngrok.io',
        '*.ngrok-free.app',
        '*.ngrok.app'
    ]
];