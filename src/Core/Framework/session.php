<?php

/**
 * Session Management for Farme Framework
 * 
 * Provides secure session handling with built-in CSRF protection
 * and user authentication state management.
 */

/**
 * Initialize session with security settings
 */
function farme_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        
        session_start();
        
        // Generate CSRF token if not exists
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
    }
}

/**
 * Set session value
 */
function farme_session_set($key, $value) {
    farme_session_start();
    $_SESSION[$key] = $value;
}

/**
 * Get session value
 */
function farme_session_get($key, $default = null) {
    farme_session_start();
    return $_SESSION[$key] ?? $default;
}

/**
 * Check if session key exists
 */
function farme_session_has($key) {
    farme_session_start();
    return isset($_SESSION[$key]);
}

/**
 * Remove session value
 */
function farme_session_remove($key) {
    farme_session_start();
    unset($_SESSION[$key]);
}

/**
 * Clear all session data
 */
function farme_session_clear() {
    farme_session_start();
    session_unset();
}

/**
 * Destroy session completely
 */
function farme_session_destroy() {
    farme_session_start();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

/**
 * Regenerate session ID for security
 */
function farme_session_regenerate() {
    farme_session_start();
    session_regenerate_id(true);
}

/**
 * Get CSRF token
 */
function farme_csrf_token() {
    farme_session_start();
    return $_SESSION['_token'];
}

/**
 * Verify CSRF token
 */
function farme_csrf_verify($token) {
    farme_session_start();
    return hash_equals($_SESSION['_token'], $token);
}

/**
 * Login user - store user data in session
 */
function farme_login($user) {
    farme_session_start();
    farme_session_regenerate(); // Security: regenerate session ID on login
    
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user',
        'status' => $user['status'] ?? true,
        'login_time' => time()
    ];
    
    $_SESSION['authenticated'] = true;
}

/**
 * Logout user - clear authentication data
 */
function farme_logout() {
    farme_session_start();
    
    // Remove user-specific data
    farme_session_remove('user');
    farme_session_remove('authenticated');
    
    // Regenerate session ID for security
    farme_session_regenerate();
}

/**
 * Check if user is authenticated
 */
function farme_is_authenticated() {
    return farme_session_get('authenticated', false) === true;
}

/**
 * Get current authenticated user
 */
function farme_current_user() {
    if (farme_is_authenticated()) {
        return farme_session_get('user');
    }
    return null;
}

/**
 * Check if user has specific role
 */
function farme_user_has_role($role) {
    $user = farme_current_user();
    return $user && ($user['role'] ?? 'user') === $role;
}

/**
 * Check if user has any of the specified roles
 */
function farme_user_has_any_role($roles) {
    $user = farme_current_user();
    if (!$user) {
        return false;
    }
    
    $userRole = $user['role'] ?? 'user';
    return in_array($userRole, (array) $roles);
}

/**
 * Check if user is admin
 */
function farme_is_admin() {
    return farme_user_has_role('admin');
}

/**
 * Check if user is editor
 */
function farme_is_editor() {
    return farme_user_has_role('editor');
}

/**
 * Check if user can access admin panel
 */
function farme_can_access_admin() {
    return farme_user_has_any_role(['admin', 'editor']);
}

/**
 * Require authentication with optional redirect
 */
function farme_require_auth($redirect_to = '/login') {
    if (!farme_is_authenticated()) {
        header("Location: $redirect_to");
        exit;
    }
}

/**
 * Require admin role with optional redirect
 */
function farme_require_admin($redirect_to = '/') {
    farme_require_auth();
    if (!farme_is_admin()) {
        farme_flash_error('Access denied. Administrator privileges required.');
        header("Location: $redirect_to");
        exit;
    }
}

/**
 * Require admin panel access with optional redirect
 */
function farme_require_admin_access($redirect_to = '/') {
    farme_require_auth();
    if (!farme_can_access_admin()) {
        farme_flash_error('Access denied. Administrator or Editor privileges required.');
        header("Location: $redirect_to");
        exit;
    }
}

/**
 * Flash messages - store temporary messages
 */
function farme_flash_set($type, $message) {
    farme_session_start();
    
    if (!isset($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }
    
    $_SESSION['_flash'][$type] = $message;
}

/**
 * Get and remove flash message
 */
function farme_flash_get($type) {
    farme_session_start();
    
    if (isset($_SESSION['_flash'][$type])) {
        $message = $_SESSION['_flash'][$type];
        unset($_SESSION['_flash'][$type]);
        return $message;
    }
    
    return null;
}

/**
 * Get all flash messages and clear them
 */
function farme_flash_all() {
    farme_session_start();
    
    $messages = $_SESSION['_flash'] ?? [];
    $_SESSION['_flash'] = [];
    
    return $messages;
}

/**
 * Helper functions for common flash message types
 */
function farme_flash_success($message) {
    farme_flash_set('success', $message);
}

function farme_flash_error($message) {
    farme_flash_set('error', $message);
}

function farme_flash_warning($message) {
    farme_flash_set('warning', $message);
}

function farme_flash_info($message) {
    farme_flash_set('info', $message);
}

/**
 * Verify CSRF token from request (wrapper function)
 */
function farme_verify_csrf() {
    $token = $_POST['_token'] ?? $_GET['_token'] ?? '';
    
    if (!$token || !farme_csrf_verify($token)) {
        http_response_code(419);
        die('CSRF token mismatch');
    }
}