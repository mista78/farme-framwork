<?php

/**
 * Application Configuration
 */

return [
    'name' => 'Farme Framework',
    'env' => 'development',
    'debug' => true,
    'timezone' => 'UTC',
    'locale' => 'en',
    
    'paths' => [
        'templates' => TEMPLATES_PATH,
        'controllers' => CONTROLLERS_PATH,
        'models' => MODELS_PATH,
        'logs' => LOG_PATH,
        'cache' => ROOT_PATH . '/cache',
    ],
    
    'session' => [
        'name' => 'farme_session',
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    
    'security' => [
        'csrf_protection' => true,
        'xss_protection' => true,
        'content_type_sniffing' => false,
        'frame_options' => 'DENY',
    ],
];