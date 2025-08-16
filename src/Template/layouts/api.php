<?php
/**
 * API Layout - JSON/XML responses
 * This layout is used for API endpoints that don't need HTML
 */

// Set appropriate headers
if (isset($content_type)) {
    header('Content-Type: ' . $content_type);
} else {
    header('Content-Type: application/json');
}

if (isset($status_code)) {
    http_response_code($status_code);
}

// CORS headers if enabled
if (farme_config_get('services.api.cors.enabled', false)) {
    $allowed_origins = farme_config_get('services.api.cors.allowed_origins', ['*']);
    if (in_array('*', $allowed_origins) || in_array($_SERVER['HTTP_ORIGIN'] ?? '', $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
    }
    
    $allowed_methods = farme_config_get('services.api.cors.allowed_methods', ['GET', 'POST']);
    header('Access-Control-Allow-Methods: ' . implode(', ', $allowed_methods));
    
    $allowed_headers = farme_config_get('services.api.cors.allowed_headers', ['Content-Type']);
    header('Access-Control-Allow-Headers: ' . implode(', ', $allowed_headers));
}

// Output content (should be JSON, XML, or plain text)
echo $content;