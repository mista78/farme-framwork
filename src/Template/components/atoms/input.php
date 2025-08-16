<?php
/**
 * Input Atom Component - FarmeCSS Version
 * 
 * Props:
 * - type: text|email|password|number|tel|url|search (default: text)
 * - name: input name
 * - value: input value
 * - placeholder: placeholder text
 * - required: boolean
 * - disabled: boolean
 * - readonly: boolean
 * - id: input id
 * - classes: additional CSS classes
 * - attributes: additional HTML attributes
 */

$type = $type ?? 'text';
$name = $name ?? '';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$disabled = $disabled ?? false;
$readonly = $readonly ?? false;
$id = $id ?? '';
$classes = $classes ?? '';
$attributes = $attributes ?? [];

// Base input classes using FarmeCSS utilities
$base_classes = [
    'f-block',
    'f-w-full',
    'f-px-3',
    'f-py-2',
    'f-text-base',
    'f-leading-6',
    'f-text-gray-700',
    'f-bg-white',
    'f-border',
    'f-border-gray-300',
    'f-rounded-lg',
    'f-shadow-sm',
    'f-transition-all',
    'f-duration-200',
    'focus:f-border-blue-500',
    'focus:f-ring-2',
    'focus:f-ring-blue-200',
    'focus:f-ring-opacity-50',
    'focus:f-outline-none'
];

// State-specific classes
$state_classes = [];

if ($disabled) {
    $state_classes = array_merge($state_classes, [
        'f-bg-gray-100',
        'f-cursor-not-allowed',
        'f-opacity-75'
    ]);
} elseif ($readonly) {
    $state_classes = array_merge($state_classes, [
        'f-bg-gray-50',
        'f-cursor-default'
    ]);
}

// Build final CSS classes
$css_classes = array_merge($base_classes, $state_classes);

// Add custom classes
if ($classes) {
    if (is_array($classes)) {
        $css_classes = array_merge($css_classes, $classes);
    } else {
        $css_classes[] = $classes;
    }
}

$class_string = implode(' ', array_filter($css_classes));

// Build attributes
$attrs = array_merge([
    'type' => $type,
    'name' => $name,
    'value' => $value,
    'class' => $class_string,
    'id' => $id,
    'placeholder' => $placeholder
], $attributes);

if ($required) {
    $attrs['required'] = true;
}

if ($disabled) {
    $attrs['disabled'] = true;
}

if ($readonly) {
    $attrs['readonly'] = true;
}
?>

<input <?= farme_attributes($attrs) ?>>

