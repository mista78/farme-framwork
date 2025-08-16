<?php
/**
 * Badge Atom Component - FarmeCSS Version
 * 
 * Props:
 * - text: Badge text
 * - variant: primary|secondary|success|danger|warning|info|light|dark (default: primary)
 * - size: sm|md|lg (default: md)
 * - pill: boolean - makes badge pill-shaped
 * - classes: additional CSS classes
 * - attributes: additional HTML attributes
 */

$text = $text ?? 'Badge';
$variant = $variant ?? 'primary';
$size = $size ?? 'md';
$pill = $pill ?? false;
$classes = $classes ?? '';
$attributes = $attributes ?? [];

// Base badge classes using FarmeCSS utilities
$base_classes = [
    'f-inline-block',
    'f-text-center',
    'f-font-bold',
    'f-leading-none',
    'f-whitespace-nowrap'
];

// Size variants
$size_classes = [
    'sm' => ['f-text-xs', 'f-px-2', 'f-py-1'],
    'md' => ['f-text-xs', 'f-px-2.5', 'f-py-1'],
    'lg' => ['f-text-sm', 'f-px-3', 'f-py-1.5']
];

// Color variants
$variant_classes = [
    'primary' => ['f-bg-blue-600', 'f-text-white'],
    'secondary' => ['f-bg-gray-600', 'f-text-white'],
    'success' => ['f-bg-green-600', 'f-text-white'],
    'danger' => ['f-bg-red-600', 'f-text-white'],
    'warning' => ['f-bg-yellow-500', 'f-text-yellow-900'],
    'info' => ['f-bg-cyan-600', 'f-text-white'],
    'light' => ['f-bg-gray-100', 'f-text-gray-800'],
    'dark' => ['f-bg-gray-800', 'f-text-white']
];

// Build final CSS classes
$css_classes = array_merge(
    $base_classes,
    $size_classes[$size] ?? $size_classes['md'],
    $variant_classes[$variant] ?? $variant_classes['primary']
);

// Add pill styling
if ($pill) {
    $css_classes[] = 'f-rounded-full';
    $css_classes[] = 'f-px-3';
} else {
    $css_classes[] = 'f-rounded';
}

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
    'class' => $class_string
], $attributes);
?>

<span <?= farme_attributes($attrs) ?>>
    <?= farme_escape($text) ?>
</span>

