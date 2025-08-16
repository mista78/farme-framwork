<?php

/**
 * Configuration Manager
 * 
 * Loads and manages configuration files
 */

// Global configuration storage
$farme_config_cache = [];

/**
 * Load configuration file
 */
function farme_load_config($file) {
    global $farme_config_cache;
    
    if (isset($farme_config_cache[$file])) {
        return $farme_config_cache[$file];
    }
    
    $config_path = CONFIG_PATH . '/' . $file . '.php';
    
    if (!file_exists($config_path)) {
        throw new Exception("Configuration file not found: {$file}");
    }
    
    $config = require $config_path;
    $farme_config_cache[$file] = $config;
    
    return $config;
}

/**
 * Get configuration value using dot notation
 */
function farme_config_get($key, $default = null) {
    $parts = explode('.', $key);
    $file = array_shift($parts);
    
    $config = farme_load_config($file);
    
    foreach ($parts as $part) {
        if (!isset($config[$part])) {
            return $default;
        }
        $config = $config[$part];
    }
    
    return $config;
}

/**
 * Set configuration value
 */
function farme_config_set($key, $value) {
    global $farme_config_cache;
    
    $parts = explode('.', $key);
    $file = array_shift($parts);
    
    if (!isset($farme_config_cache[$file])) {
        farme_load_config($file);
    }
    
    $config = &$farme_config_cache[$file];
    
    foreach ($parts as $part) {
        if (!isset($config[$part])) {
            $config[$part] = [];
        }
        $config = &$config[$part];
    }
    
    $config = $value;
}

/**
 * Check if configuration key exists
 */
function farme_config_has($key) {
    $parts = explode('.', $key);
    $file = array_shift($parts);
    
    try {
        $config = farme_load_config($file);
        
        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                return false;
            }
            $config = $config[$part];
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get environment variable with fallback
 */
function farme_env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Convert string boolean values
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
    }
    
    return $value;
}

/**
 * Load environment variables from .env file
 */
function farme_load_env($path = null) {
    if ($path === null) {
        $path = ROOT_PATH . '/.env';
    }
    
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
    }
}