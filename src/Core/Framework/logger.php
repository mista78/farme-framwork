<?php

/**
 * Advanced Logging System for Farme Framework
 * 
 * Features:
 * - Daily log file organization
 * - Multiple log levels (ERROR, WARNING, INFO, DEBUG)
 * - Structured logging with context
 * - Error tracking and analysis
 * - Performance monitoring
 * - Request logging
 */

/**
 * Log levels constants
 */
define('FARME_LOG_ERROR', 'ERROR');
define('FARME_LOG_WARNING', 'WARNING');
define('FARME_LOG_INFO', 'INFO');
define('FARME_LOG_DEBUG', 'DEBUG');
define('FARME_LOG_ACCESS', 'ACCESS');

/**
 * Initialize logging system
 */
function farme_logger_init() {
    // Ensure log directories exist
    $log_dirs = ['error', 'access', 'debug', 'application'];
    
    foreach ($log_dirs as $dir) {
        $path = ROOT_PATH . "/logs/{$dir}";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
    
    // Set up custom error handlers
    set_error_handler('farme_error_handler');
    set_exception_handler('farme_exception_handler');
    register_shutdown_function('farme_shutdown_handler');
}

/**
 * Main logging function
 */
function farme_log($level, $message, $context = [], $category = 'application') {
    $timestamp = date('Y-m-d H:i:s');
    $date = date('Y-m-d');
    $request_id = farme_get_request_id();
    
    // Prepare log entry
    $log_entry = [
        'timestamp' => $timestamp,
        'level' => strtoupper($level),
        'message' => $message,
        'request_id' => $request_id,
        'context' => $context,
        'memory_usage' => farme_format_bytes(memory_get_usage(true)),
        'peak_memory' => farme_format_bytes(memory_get_peak_usage(true))
    ];
    
    // Add additional context based on level
    if (in_array($level, [FARME_LOG_ERROR, FARME_LOG_WARNING])) {
        $log_entry['stack_trace'] = farme_get_stack_trace();
        $log_entry['url'] = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $log_entry['method'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $log_entry['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
        $log_entry['ip'] = farme_get_client_ip();
    }
    
    // Format log line
    $formatted_line = farme_format_log_line($log_entry);
    
    // Write to appropriate log file
    $log_file = farme_get_log_file($category, $date);
    farme_write_log($log_file, $formatted_line);
    
    // Also write to daily combined log
    $combined_file = farme_get_log_file('application', $date);
    if ($combined_file !== $log_file) {
        farme_write_log($combined_file, $formatted_line);
    }
    
    // Handle critical errors
    if ($level === FARME_LOG_ERROR) {
        farme_handle_critical_error($log_entry);
    }
}

/**
 * Convenience logging functions
 */
function farme_log_error($message, $context = []) {
    farme_log(FARME_LOG_ERROR, $message, $context, 'error');
}

function farme_log_warning($message, $context = []) {
    farme_log(FARME_LOG_WARNING, $message, $context, 'application');
}

function farme_log_info($message, $context = []) {
    farme_log(FARME_LOG_INFO, $message, $context, 'application');
}

function farme_log_debug($message, $context = []) {
    if (farme_is_debug_enabled()) {
        farme_log(FARME_LOG_DEBUG, $message, $context, 'debug');
    }
}

function farme_log_access($message, $context = []) {
    farme_log(FARME_LOG_ACCESS, $message, $context, 'access');
}

/**
 * Error handlers
 */
function farme_error_handler($severity, $message, $file, $line) {
    // Don't log suppressed errors
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $error_types = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_STRICT => 'Strict Standards',
        E_DEPRECATED => 'Deprecated',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice'
    ];
    
    $error_type = $error_types[$severity] ?? 'Unknown Error';
    $log_level = in_array($severity, [E_ERROR, E_USER_ERROR, E_PARSE]) ? FARME_LOG_ERROR : FARME_LOG_WARNING;
    
    $context = [
        'type' => $error_type,
        'severity' => $severity,
        'file' => $file,
        'line' => $line,
        'error_code' => $severity
    ];
    
    farme_log($log_level, $message, $context, 'error');
    
    // Don't execute PHP internal error handler
    return true;
}

function farme_exception_handler($exception) {
    $context = [
        'type' => get_class($exception),
        'code' => $exception->getCode(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    
    farme_log_error('Uncaught Exception: ' . $exception->getMessage(), $context);
    
    // Show user-friendly error page in production
    if (!farme_is_debug_enabled()) {
        http_response_code(500);
        echo "Internal Server Error";
        exit;
    }
}

function farme_shutdown_handler() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $context = [
            'type' => 'Fatal Error',
            'file' => $error['file'],
            'line' => $error['line'],
            'shutdown' => true
        ];
        
        farme_log_error($error['message'], $context);
    }
    
    // Log request completion
    farme_log_request_completion();
}

/**
 * Request logging
 */
function farme_log_request_start() {
    $context = [
        'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'ip' => farme_get_client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
        'referer' => $_SERVER['HTTP_REFERER'] ?? '',
        'request_time' => $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)
    ];
    
    farme_log_access('Request started', $context);
}

function farme_log_request_completion() {
    $execution_time = microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    
    $context = [
        'execution_time' => round($execution_time * 1000, 2) . 'ms',
        'memory_peak' => farme_format_bytes(memory_get_peak_usage(true)),
        'response_code' => http_response_code() ?: 200
    ];
    
    farme_log_access('Request completed', $context);
}

/**
 * Helper functions
 */
function farme_get_request_id() {
    static $request_id = null;
    
    if ($request_id === null) {
        $request_id = substr(md5(uniqid() . microtime()), 0, 8);
    }
    
    return $request_id;
}

function farme_get_client_ip() {
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ip_headers as $header) {
        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function farme_get_stack_trace($skip = 2) {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
    $filtered_trace = array_slice($trace, $skip);
    
    $stack = [];
    foreach ($filtered_trace as $frame) {
        $line = '';
        if (isset($frame['file'])) {
            $line .= basename($frame['file']);
            if (isset($frame['line'])) {
                $line .= ':' . $frame['line'];
            }
        }
        
        if (isset($frame['function'])) {
            $line .= ' ' . $frame['function'] . '()';
        }
        
        if ($line) {
            $stack[] = $line;
        }
    }
    
    return implode(' -> ', $stack);
}

function farme_format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function farme_is_debug_enabled() {
    return (farme_config_get('app.debug', false) || getenv('DEBUG') === 'true');
}

function farme_get_log_file($category, $date) {
    $log_dir = ROOT_PATH . "/logs/{$category}";
    return "{$log_dir}/{$date}.log";
}

function farme_format_log_line($log_entry) {
    $line = "[{$log_entry['timestamp']}] ";
    $line .= "[{$log_entry['level']}] ";
    $line .= "[{$log_entry['request_id']}] ";
    $line .= $log_entry['message'];
    
    if (!empty($log_entry['context'])) {
        $line .= " | Context: " . json_encode($log_entry['context'], JSON_UNESCAPED_SLASHES);
    }
    
    $line .= " | Memory: {$log_entry['memory_usage']} | Peak: {$log_entry['peak_memory']}";
    
    return $line . PHP_EOL;
}

function farme_write_log($file, $content) {
    // Ensure directory exists
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Write to file with locking
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
}

function farme_handle_critical_error($log_entry) {
    // Count errors for rate limiting
    static $error_count = 0;
    $error_count++;
    
    // Prevent error loops
    if ($error_count > 10) {
        return;
    }
    
    // Additional critical error handling can be added here
    // For example: send notifications, trigger alerts, etc.
}

/**
 * Log analysis functions
 */
function farme_get_log_stats($date = null) {
    $date = $date ?: date('Y-m-d');
    $stats = ['errors' => 0, 'warnings' => 0, 'requests' => 0, 'avg_response_time' => 0];
    
    // Error log stats
    $error_file = farme_get_log_file('error', $date);
    if (file_exists($error_file)) {
        $content = file_get_contents($error_file);
        $stats['errors'] = substr_count($content, '[ERROR]');
        $stats['warnings'] = substr_count($content, '[WARNING]');
    }
    
    // Access log stats
    $access_file = farme_get_log_file('access', $date);
    if (file_exists($access_file)) {
        $lines = file($access_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $response_times = [];
        
        foreach ($lines as $line) {
            if (strpos($line, 'Request completed') !== false) {
                $stats['requests']++;
                
                // Extract response time
                if (preg_match('/execution_time":"([0-9.]+)ms/', $line, $matches)) {
                    $response_times[] = (float)$matches[1];
                }
            }
        }
        
        if (!empty($response_times)) {
            $stats['avg_response_time'] = round(array_sum($response_times) / count($response_times), 2);
        }
    }
    
    return $stats;
}

/**
 * Log cleanup function
 */
function farme_cleanup_old_logs($days = 30) {
    $log_dirs = ['error', 'access', 'debug', 'application'];
    $cutoff_time = time() - ($days * 24 * 60 * 60);
    $deleted_files = 0;
    
    foreach ($log_dirs as $dir) {
        $log_dir = ROOT_PATH . "/logs/{$dir}";
        if (!is_dir($log_dir)) {
            continue;
        }
        
        $files = glob($log_dir . '/*.log');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
                $deleted_files++;
            }
        }
    }
    
    return $deleted_files;
}