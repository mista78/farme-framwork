<?php

/**
 * Template rendering system
 */

/**
 * Render template with data and layout
 */
function farme_render($template, $data = [], $layout = 'default') {
    $templates_path = farme_config_get('app.paths.templates');
    $template_path = $templates_path . '/' . $template . '.php';
    
    if (!file_exists($template_path)) {
        return farme_error("Template not found: $template");
    }
    
    // Extract data to variables
    extract($data);
    
    // Start output buffering for template content
    ob_start();
    
    // Include template
    include $template_path;
    
    // Get template content
    $template_content = ob_get_clean();
    
    // If no layout specified or layout is false, return content directly
    if ($layout === false || $layout === null) {
        return $template_content;
    }
    
    // Render with layout
    return farme_render_with_layout($template_content, $data, $layout);
}

/**
 * Render content with specified layout
 */
function farme_render_with_layout($content, $data = [], $layout = 'default') {
    $templates_path = farme_config_get('app.paths.templates');
    $layout_path = $templates_path . '/layouts/' . $layout . '.php';
    
    if (!file_exists($layout_path)) {
        return farme_error("Layout not found: $layout");
    }
    
    // Add flash messages to data if session functions are available

    if(function_exists('farme_current_user')) {
        $data["user"] = farme_current_user();
    }

    if (function_exists('farme_flash_all')) {
        $data['flash_messages'] = farme_flash_all();
    }
    
    // Extract data to variables
    extract($data);
    
    // Start output buffering for layout
    ob_start();
    
    // Include layout (which will use $content variable)
    include $layout_path;
    
    // Get final output
    $final_content = ob_get_clean();
    
    return $final_content;
}

/**
 * Render template without layout
 */
function farme_render_partial($template, $data = []) {
    return farme_render($template, $data, false);
}

/**
 * Set default layout for current request
 */
function farme_set_layout($layout) {
    global $farme_default_layout;
    $farme_default_layout = $layout;
}

/**
 * Get current default layout
 */
function farme_get_layout() {
    global $farme_default_layout;
    return $farme_default_layout ?? 'default';
}

/**
 * Render with current default layout
 */
function farme_render_default($template, $data = []) {
    return farme_render($template, $data, farme_get_layout());
}

/**
 * Render JSON response with API layout
 */
function farme_json($data, $status_code = 200) {
    $json_content = json_encode($data);
    return farme_render_with_layout($json_content, [
        'status_code' => $status_code,
        'content_type' => 'application/json'
    ], 'api');
}

/**
 * Render XML response with API layout
 */
function farme_xml($data, $status_code = 200, $root_element = 'response') {
    $xml_content = farme_array_to_xml($data, $root_element);
    return farme_render_with_layout($xml_content, [
        'status_code' => $status_code,
        'content_type' => 'application/xml'
    ], 'api');
}

/**
 * Convert array to XML
 */
function farme_array_to_xml($array, $root_element = 'root', $xml = null) {
    if ($xml === null) {
        $xml = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><{$root_element}></{$root_element}>");
    }
    
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            farme_array_to_xml($value, $key, $xml->addChild($key));
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
    
    return $xml->asXML();
}

/**
 * Render plain text response
 */
function farme_text($text, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: text/plain');
    return $text;
}

/**
 * Redirect to URL
 */
function farme_redirect($url, $status_code = 302) {
    http_response_code($status_code);
    header("Location: $url");
    exit;
}

/**
 * Include partial template
 */
function farme_partial($partial, $data = []) {
    $partial_path = farme_config_get('app.paths.templates') . '/partials/' . $partial . '.php';
    
    if (!file_exists($partial_path)) {
        return "<!-- Partial not found: $partial -->";
    }
    
    extract($data);
    include $partial_path;
}

/**
 * Escape HTML output
 */
function farme_escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL for route
 */
function farme_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $protocol . '://' . $host;
    
    return $base . '/' . ltrim($path, '/');
}

/**
 * Include CSS file
 */
function farme_css($file) {
    $url = farme_url('css/' . $file);
    return "<link rel=\"stylesheet\" href=\"$url\">";
}

/**
 * Include JS file
 */
function farme_js($file) {
    $url = farme_url('js/' . $file);
    return "<script src=\"$url\"></script>";
}