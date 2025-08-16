<?php

/**
 * Bootstrap Configuration
 * 
 * Defines how the framework should be initialized
 */

return [
    'autoload' => [
        'controllers' => true,
        'models' => true,
        'helpers' => true,
    ],
    
    'middleware' => [
        'global' => [
            'security_headers',
            'cors',
        ],
        'web' => [
            'session',
            'csrf_protection',
        ],
        'api' => [
            'rate_limit',
            'json_response',
        ],
    ],
    
    'providers' => [
        'database',
        'template',
        'router',
        'console',
    ],
    
    'aliases' => [
        'db' => 'farme_db',
        'render' => 'farme_render',
        'redirect' => 'farme_redirect',
        'config' => 'farme_config',
        'route' => 'farme_add_route',
    ],
    
    'error_handling' => [
        'display_errors' => true,
        'log_errors' => true,
        'error_reporting' => E_ALL,
    ],
];