<?php

return [
    // License validation settings
    'validation' => [
        'valid_error_codes' => [
            '0xC004C008',
            'Online Key',
        ],
        'api_timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 5, // seconds
    ],
    
    // Activation settings
    'activation' => [
        'installation_id_lengths' => [54, 63],
        'rate_limit' => [
            'attempts' => 5,
            'decay_minutes' => 60,
        ],
        'auto_retry_failed' => true,
        'retry_delay' => 300, // seconds
    ],
    
    // License pool settings
    'pool' => [
        'low_stock_threshold' => 10,
        'bulk_upload_limit' => 100,
        'auto_cleanup_invalid' => true,
        'cleanup_after_days' => 7,
    ],
    
    // CID generation settings
    'cid' => [
        'api_timeout' => 30,
        'cache_ttl' => 300, // 5 minutes
    ],
];