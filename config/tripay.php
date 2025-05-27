<?php

return [
    'api_key' => env('TRIPAY_API_KEY', 'DEV-6fIeMLEhMGrZ9BuAUcuQMBDY4QPnfZMQwgKTAeUk'),
    'private_key' => env('TRIPAY_PRIVATE_KEY', 'pV4xm-d3t5T-d0GfU-qU3PA-WSsYI'),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', 'T34697'),
    'base_url' => env('TRIPAY_BASE_URL', 'https://tripay.co.id/api-sandbox'),
    'callback_url' => env('TRIPAY_CALLBACK_URL', null),
    'return_url' => env('TRIPAY_RETURN_URL', null),
];
