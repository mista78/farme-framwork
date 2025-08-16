<?php

/**
 * Discover routes from controller annotations
 */
function farme_discover_routes() {
    global $farme_routes;
    
    $controllers_path = farme_config_get('app.paths.controllers');
    $controller_files = glob($controllers_path . '/*.php');
    
    foreach ($controller_files as $file) {
        $content = file_get_contents($file);
        $controller_name = basename($file, '.php');
        
        // Extract functions and their annotations
        preg_match_all('/\/\*\*(.*?)\*\/\s*function\s+(\w+)/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $comment = $match[1];
            $function_name = $match[2];
            
            // Extract route annotations
            preg_match_all('/@route\s+(GET|POST|PUT|DELETE|PATCH)\s+([^\s\*]+)/i', $comment, $route_matches, PREG_SET_ORDER);
            
            foreach ($route_matches as $route_match) {
                $method = strtoupper($route_match[1]);
                $path = trim($route_match[2]);
                
                // Extract controller and action from function name
                // Support both simple (controller_action) and complex (controller_sub_action) patterns
                if (preg_match('/^(\w+)_(.+)$/', $function_name, $func_parts)) {
                    $controller = $func_parts[1];
                    $action = $func_parts[2];
                    
                    // Special handling for admin sub-controllers (admin_posts_*, admin_users_*)
                    // Keep them as separate entities instead of mapping to non-existent admin controller
                    if (preg_match('/^admin_(\w+)_(.+)$/', $function_name, $admin_parts)) {
                        $controller = 'admin_' . $admin_parts[1]; // admin_posts, admin_users, etc.
                        $action = $admin_parts[2]; // index, show, create, etc.
                    }
                    
                    $farme_routes[] = [
                        'method' => $method,
                        'path' => $path,
                        'controller' => $controller,
                        'action' => $action,
                        'function' => $function_name
                    ];
                }
            }
        }
    }
}

/**
 * Add manual route
 */
function farme_add_route($method, $path, $controller, $action) {
    global $farme_routes;
    
    $farme_routes[] = [
        'method' => strtoupper($method),
        'path' => $path,
        'controller' => $controller,
        'action' => $action,
        'function' => strtolower($controller) . '_' . $action
    ];
}

/**
 * Get all registered routes
 */
function farme_get_routes() {
    global $farme_routes;
    return $farme_routes;
}

/**
 * Clear all routes
 */
function farme_clear_routes() {
    global $farme_routes;
    $farme_routes = [];
}