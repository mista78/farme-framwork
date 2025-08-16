<?php

/**
 * Database Configuration
 * 
 * Support for multiple database connections
 */

return [
    'default' => 'mysql_main',
    
    'connections' => [
        'main' => [
            'driver' => 'sqlite',
            'database' => STORAGE_PATH . '/database.sqlite',
            'prefix' => '',
        ],
        
        'mysql_main' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'frame',
            'username' => 'root',
            'password' => 'kmaoulida',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        
        'postgres' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => 5432,
            'database' => 'farme_postgres',
            'username' => 'postgres',
            'password' => '',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],
        
        'redis_cache' => [
            'driver' => 'redis',
            'host' => 'localhost',
            'port' => 6379,
            'database' => 0,
            'prefix' => 'farme:cache:',
        ],
    ],
    
    'migration' => [
        'table' => 'migrations',
        'path' => ROOT_PATH . '/database/migrations',
    ],
    
    'seed' => [
        'path' => ROOT_PATH . '/database/seeds',
    ],
];