<?php

return [
    // Warranty period in days
    'period_days' => 7,
    
    // Auto-approval settings
    'auto_approval' => [
        'enabled' => true,
        'conditions' => [
            'status_must_be' => 'blocked',
            'within_warranty_period' => true,
            'no_successful_activation' => true,
            'not_previously_replaced' => true,
            'has_activation_attempts' => true,
        ],
    ],
    
    // Claim limits
    'limits' => [
        'max_claims_per_license' => 1,
        'rate_limit' => [
            'attempts' => 3,
            'decay_minutes' => 1440, // 24 hours
        ],
    ],
    
    // Notification settings
    'notifications' => [
        'admin_on_manual_claim' => true,
        'user_on_approval' => true,
        'user_on_rejection' => true,
    ],
];