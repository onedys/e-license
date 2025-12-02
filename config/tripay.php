<?php

return [
    'mode' => env('TRIPAY_MODE', 'sandbox'),
    
    // Flat config untuk backward compatibility
    'api_key' => function () {
        $mode = config('tripay.mode', 'sandbox');
        return config("tripay.{$mode}.api_key") ?? env('TRIPAY_API_KEY');
    },
    
    'private_key' => function () {
        $mode = config('tripay.mode', 'sandbox');
        return config("tripay.{$mode}.private_key") ?? env('TRIPAY_PRIVATE_KEY');
    },
    
    'merchant_code' => function () {
        $mode = config('tripay.mode', 'sandbox');
        return config("tripay.{$mode}.merchant_code") ?? env('TRIPAY_MERCHANT_CODE');
    },
    
    // Nested config untuk future use
    'sandbox' => [
        'api_key' => env('TRIPAY_SANDBOX_API_KEY'),
        'private_key' => env('TRIPAY_SANDBOX_PRIVATE_KEY'),
        'merchant_code' => env('TRIPAY_SANDBOX_MERCHANT_CODE'),
    ],
    
    'production' => [
        'api_key' => env('TRIPAY_PRODUCTION_API_KEY'),
        'private_key' => env('TRIPAY_PRODUCTION_PRIVATE_KEY'),
        'merchant_code' => env('TRIPAY_PRODUCTION_MERCHANT_CODE'),
    ],
];