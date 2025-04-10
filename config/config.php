<?php

declare(strict_types=1);

/*
 * You can place your custom package configuration in here.
 */
return [
    'default_owner_key' => 'user_id',
    'default_override_key' => 'author_id',
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'fortress:auth:',
    ],
    
    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],
    
    'audit_logging' => [
        'enabled' => true,
        'channel' => 'stack',
    ],
];
