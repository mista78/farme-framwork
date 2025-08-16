<?php

/**
 * Farme Framework Bootstrap
 * 
 * This file loads all framework components and initializes the system
 */

// Load environment variables
require_once CORE_PATH . '/Framework/config.php';
farme_load_env();

// Include core framework files
require_once CORE_PATH . '/Framework/core.php';
require_once CORE_PATH . '/Framework/logger.php';
require_once CORE_PATH . '/Framework/session.php';
require_once CORE_PATH . '/Router/router.php';
require_once CORE_PATH . '/Template/template.php';
require_once CORE_PATH . '/Template/components.php';
require_once CORE_PATH . '/Database/database.php';
require_once CORE_PATH . '/Database/connection_manager.php';
require_once CORE_PATH . '/Database/query_builder.php';
require_once CORE_PATH . '/Database/orm.php';
require_once CORE_PATH . '/Database/schema.php';
require_once CORE_PATH . '/Database/migrations.php';
require_once CORE_PATH . '/Console/console.php';
require_once CORE_PATH . '/Console/commands.php';

// Auto-load all controllers
function farme_autoload_controllers() {
    $controllers_path = farme_config_get('app.paths.controllers');
    if (is_dir($controllers_path)) {
        $controller_files = glob($controllers_path . '/*.php');
        
        foreach ($controller_files as $file) {
            require_once $file;
        }
    }
}

// Auto-load all models
function farme_autoload_models() {
    $models_path = farme_config_get('app.paths.models');
    if (is_dir($models_path)) {
        $model_files = glob($models_path . '/*.php');
        
        foreach ($model_files as $file) {
            require_once $file;
        }
    }
}

/**
 * Initialize the framework for web requests
 */
function farme_boot_web($config = []) {
    // Initialize logging system early
    farme_logger_init();
    farme_log_info('Framework boot started', ['type' => 'web']);
    farme_log_request_start();
    
    // Load bootstrap configuration
    $bootstrap_config = farme_load_config('bootstrap');
    
    // Initialize framework with merged config
    $app_config = farme_load_config('app');
    farme_init(array_merge($app_config, $config));
    
    // Initialize database connections
    $db_config = farme_load_config('database');
    farme_db_configure($db_config);
    
    // Auto-load controllers and models if enabled
    if ($bootstrap_config['autoload']['controllers']) {
        farme_autoload_controllers();
    }
    if ($bootstrap_config['autoload']['models']) {
        farme_autoload_models();
    }
    
    // Discover routes after controllers are loaded
    farme_discover_routes();
    
    // Handle the request
    try {
        $response = farme_handle_request();
        farme_log_info('Request handled successfully');
        
        // Output response
        echo $response;
    } catch (Exception $e) {
        farme_log_error('Request handling failed: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Re-throw for proper error handling
        throw $e;
    }
}

/**
 * Initialize the framework for console commands
 */
function farme_boot_console($argv) {
    // Initialize logging system for console
    farme_logger_init();
    farme_log_info('Framework console boot started', ['command' => implode(' ', $argv)]);
    
    // Load bootstrap configuration
    $bootstrap_config = farme_load_config('bootstrap');
    
    // Initialize framework
    $app_config = farme_load_config('app');
    farme_init($app_config);
    
    // Initialize database connections
    $db_config = farme_load_config('database');
    farme_db_configure($db_config);
    
    // Auto-load controllers and models if enabled
    if ($bootstrap_config['autoload']['controllers']) {
        farme_autoload_controllers();
    }
    if ($bootstrap_config['autoload']['models']) {
        farme_autoload_models();
    }
    
    // Discover routes after controllers are loaded
    farme_discover_routes();
    
    // Register default commands
    farme_register_default_commands();
    
    // Run command
    try {
        farme_run_command($argv);
        farme_log_info('Console command completed successfully');
    } catch (Exception $e) {
        farme_log_error('Console command failed: ' . $e->getMessage(), [
            'command' => implode(' ', $argv),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        // Re-throw for proper error handling
        throw $e;
    }
}