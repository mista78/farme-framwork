<?php

// Global framework state
$farme_routes = [];
$farme_config = [];
$farme_templates_path = TEMPLATES_PATH;
$farme_controllers_path = CONTROLLERS_PATH;

/**
 * Initialize the framework
 */
function farme_init($config = []) {
    global $farme_config, $farme_templates_path, $farme_controllers_path;
    
    $farme_config = array_merge([
        'debug' => true,
        'templates_path' => $farme_templates_path,
        'controllers_path' => $farme_controllers_path,
    ], $config);
}

/**
 * Get configuration value
 */
function farme_config($key, $default = null) {
    global $farme_config;
    return isset($farme_config[$key]) ? $farme_config[$key] : $default;
}

/**
 * Handle HTTP request
 */
function farme_handle_request() {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    
    return farme_dispatch($method, $uri);
}

/**
 * Dispatch request to appropriate controller
 */
function farme_dispatch($method, $uri) {
    global $farme_routes;
    
    foreach ($farme_routes as $route) {
        if ($route['method'] === $method && farme_match_route($route['path'], $uri)) {
            $params = farme_extract_params($route['path'], $uri);
            return farme_call_controller($route['controller'], $route['action'], $params);
        }
    }
    
    return farme_not_found();
}

/**
 * Match route pattern against URI
 */
function farme_match_route($pattern, $uri) {
    $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
    $pattern = str_replace('/', '\/', $pattern);
    return preg_match('/^' . $pattern . '$/', $uri);
}

/**
 * Extract parameters from URI
 */
function farme_extract_params($pattern, $uri) {
    $params = [];
    $pattern_parts = explode('/', $pattern);
    $uri_parts = explode('/', $uri);
    
    for ($i = 0; $i < count($pattern_parts); $i++) {
        if (isset($pattern_parts[$i]) && preg_match('/\{([^}]+)\}/', $pattern_parts[$i], $matches)) {
            $params[$matches[1]] = isset($uri_parts[$i]) ? $uri_parts[$i] : null;
        }
    }
    
    return $params;
}

/**
 * Call controller action
 */
function farme_call_controller($controller, $action, $params = []) {
    $controllers_path = farme_config_get('app.paths.controllers');
    $controller_file = $controllers_path . '/' . ucfirst($controller) . 'Controller.php';
    
    if (!file_exists($controller_file)) {
        return farme_error("Controller not found: $controller");
    }
    
    include_once $controller_file;
    
    // Try to find the exact function name from routes first
    $function_name = null;
    global $farme_routes;
    
    foreach ($farme_routes as $route) {
        if ($route['controller'] === $controller && $route['action'] === $action) {
            $function_name = $route['function'];
            break;
        }
    }
    
    // Fallback to old method if not found in routes
    if (!$function_name) {
        $function_name = strtolower($controller) . '_' . $action;
    }
    
    if (!function_exists($function_name)) {
        return farme_error("Action not found: $function_name");
    }
    
    return call_user_func($function_name, $params);
}

/**
 * Handle 404 errors
 */
function farme_not_found($custom_data = []) {
    http_response_code(404);
    
    // Default 404 data
    $data = array_merge([
        'title' => '404 - Page Not Found',
        'error_title' => 'Page Not Found', 
        'error_message' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
        'requested_url' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'show_debug' => farme_config_get('app.debug', false),
        'search_enabled' => false,
        'contact_enabled' => true,
    ], $custom_data);
    
    // Try to render with 404 layout
    try {
        return farme_render('errors/404', $data, '404');
    } catch (Exception $e) {
        // Fallback to simple error message if template system fails
        return '<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for could not be found.</p>
    <a href="/">← Go Home</a>
</body>
</html>';
    }
}

/**
 * Handle errors
 */
function farme_error($message) {
    if (farme_config('debug')) {
        return "Error: " . $message;
    }
    return "An error occurred";
}