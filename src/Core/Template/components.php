<?php

/**
 * Atomic Components System
 * 
 * Implements reusable UI components following Atomic Design methodology:
 * - Atoms: Basic HTML elements (buttons, inputs, labels)
 * - Molecules: Groups of atoms (form fields, search boxes)
 * - Organisms: Complex components (headers, forms, cards)
 */

// Global component cache
$farme_component_cache = [];

/**
 * Render an atomic component
 */
function farme_component($component, $props = [], $type = 'atoms') {
    $templates_path = farme_config_get('app.paths.templates');
    $component_path = $templates_path . '/components/' . $type . '/' . $component . '.php';
    
    if (!file_exists($component_path)) {
        if (farme_config_get('app.debug', true)) {
            return "<!-- Component not found: {$type}/{$component} -->";
        }
        return '';
    }
    
    // Extract props to variables
    extract($props);
    
    // Start output buffering
    ob_start();
    
    // Include component
    include $component_path;
    
    // Get contents and clean buffer
    $content = ob_get_clean();
    
    return $content;
}

/**
 * Render an atom component
 */
function farme_atom($component, $props = []) {
    return farme_component($component, $props, 'atoms');
}

/**
 * Render a molecule component
 */
function farme_molecule($component, $props = []) {
    return farme_component($component, $props, 'molecules');
}

/**
 * Render an organism component
 */
function farme_organism($component, $props = []) {
    return farme_component($component, $props, 'organisms');
}

/**
 * Register a component in cache for reuse
 */
function farme_component_cache($name, $content) {
    global $farme_component_cache;
    $farme_component_cache[$name] = $content;
}

/**
 * Get cached component
 */
function farme_component_cached($name) {
    global $farme_component_cache;
    return $farme_component_cache[$name] ?? null;
}

/**
 * Render component with caching
 */
function farme_component_with_cache($component, $props = [], $type = 'atoms', $cache_key = null) {
    if ($cache_key === null) {
        $cache_key = $type . '_' . $component . '_' . md5(serialize($props));
    }
    
    $cached = farme_component_cached($cache_key);
    if ($cached !== null) {
        return $cached;
    }
    
    $content = farme_component($component, $props, $type);
    farme_component_cache($cache_key, $content);
    
    return $content;
}

/**
 * Create a component factory function
 */
function farme_create_component($name, $defaults = []) {
    return function($props = []) use ($name, $defaults) {
        $merged_props = array_merge($defaults, $props);
        return farme_component($name, $merged_props);
    };
}

/**
 * Render multiple components
 */
function farme_components($components) {
    $output = '';
    
    foreach ($components as $component) {
        if (is_string($component)) {
            $output .= farme_atom($component);
        } elseif (is_array($component)) {
            $type = $component['type'] ?? 'atoms';
            $name = $component['name'] ?? '';
            $props = $component['props'] ?? [];
            
            $output .= farme_component($name, $props, $type);
        }
    }
    
    return $output;
}

/**
 * Component helper functions
 */

/**
 * Generate CSS classes string from array
 */
function farme_classes($classes) {
    if (is_string($classes)) {
        return $classes;
    }
    
    if (is_array($classes)) {
        $class_list = [];
        
        foreach ($classes as $key => $value) {
            if (is_numeric($key)) {
                // Simple array of class names
                if ($value) {
                    $class_list[] = $value;
                }
            } else {
                // Associative array: class => condition
                if ($value) {
                    $class_list[] = $key;
                }
            }
        }
        
        return implode(' ', $class_list);
    }
    
    return '';
}

/**
 * Generate HTML attributes from array
 */
function farme_attributes($attributes) {
    if (empty($attributes)) {
        return '';
    }
    
    $attr_list = [];
    
    foreach ($attributes as $key => $value) {
        if ($value === null || $value === false) {
            continue;
        }
        
        if ($value === true) {
            $attr_list[] = $key;
        } else {
            $attr_list[] = $key . '="' . farme_escape($value) . '"';
        }
    }
    
    return implode(' ', $attr_list);
}

/**
 * Generate inline styles from array
 */
function farme_styles($styles) {
    if (is_string($styles)) {
        return $styles;
    }
    
    if (is_array($styles)) {
        $style_list = [];
        
        foreach ($styles as $property => $value) {
            if ($value !== null && $value !== '') {
                $style_list[] = $property . ': ' . $value;
            }
        }
        
        return implode('; ', $style_list);
    }
    
    return '';
}

/**
 * Check if component exists
 */
function farme_component_exists($component, $type = 'atoms') {
    $templates_path = farme_config_get('app.paths.templates');
    $component_path = $templates_path . '/components/' . $type . '/' . $component . '.php';
    
    return file_exists($component_path);
}

/**
 * List all components of a type
 */
function farme_list_components($type = 'atoms') {
    $templates_path = farme_config_get('app.paths.templates');
    $components_path = $templates_path . '/components/' . $type;
    
    if (!is_dir($components_path)) {
        return [];
    }
    
    $components = [];
    $files = glob($components_path . '/*.php');
    
    foreach ($files as $file) {
        $components[] = basename($file, '.php');
    }
    
    return $components;
}