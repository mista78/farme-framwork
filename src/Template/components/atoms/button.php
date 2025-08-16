<?php
/**
 * Button Atom Component - FarmeCSS Version
 * 
 * Props:
 * - text: Button text
 * - type: button|submit|reset (default: button)
 * - variant: primary|secondary|success|danger|warning|info|light|dark (default: primary)
 * - size: sm|md|lg (default: md)
 * - disabled: boolean
 * - classes: additional CSS classes
 * - attributes: additional HTML attributes
 * - href: if provided, renders as link instead of button
 */

$text = $text ?? 'Button';
$type = $type ?? 'button';
$variant = $variant ?? 'primary';
$size = $size ?? 'md';
$disabled = $disabled ?? false;
$classes = $classes ?? '';
$attributes = $attributes ?? [];
$href = $href ?? null;

// Base button classes using FarmeCSS utilities
$base_classes = [
    'f-inline-flex',
    'f-items-center',
    'f-justify-center',
    'f-px-4',
    'f-py-2',
    'f-text-sm',
    'f-font-medium',
    'f-text-center',
    'f-border',
    'f-rounded-lg',
    'f-cursor-pointer',
    'f-transition-all',
    'f-duration-200',
    'f-leading-5'
];

// Size variants
$size_classes = [
    'sm' => ['f-px-3', 'f-py-1.5', 'f-text-xs'],
    'md' => ['f-px-4', 'f-py-2', 'f-text-sm'],
    'lg' => ['f-px-6', 'f-py-3', 'f-text-base']
];

// Color variants
$variant_classes = [
    'primary' => [
        'f-bg-blue-600',
        'f-border-blue-600',
        'f-text-white',
        'f-hover:bg-blue-700',
        'f-hover:border-blue-700',
        'f-focus:ring-2',
        'f-focus:ring-blue-500',
        'f-focus:ring-opacity-50'
    ],
    'secondary' => [
        'f-bg-gray-600',
        'f-border-gray-600',
        'f-text-white',
        'f-hover:bg-gray-700',
        'f-hover:border-gray-700',
        'f-focus:ring-2',
        'f-focus:ring-gray-500',
        'f-focus:ring-opacity-50'
    ],
    'success' => [
        'f-bg-green-600',
        'f-border-green-600',
        'f-text-white',
        'f-hover:bg-green-700',
        'f-hover:border-green-700',
        'f-focus:ring-2',
        'f-focus:ring-green-500',
        'f-focus:ring-opacity-50'
    ],
    'danger' => [
        'f-bg-red-600',
        'f-border-red-600',
        'f-text-white',
        'f-hover:bg-red-700',
        'f-hover:border-red-700',
        'f-focus:ring-2',
        'f-focus:ring-red-500',
        'f-focus:ring-opacity-50'
    ],
    'warning' => [
        'f-bg-yellow-500',
        'f-border-yellow-500',
        'f-text-yellow-900',
        'f-hover:bg-yellow-600',
        'f-hover:border-yellow-600',
        'f-focus:ring-2',
        'f-focus:ring-yellow-500',
        'f-focus:ring-opacity-50'
    ],
    'info' => [
        'f-bg-cyan-600',
        'f-border-cyan-600',
        'f-text-white',
        'f-hover:bg-cyan-700',
        'f-hover:border-cyan-700',
        'f-focus:ring-2',
        'f-focus:ring-cyan-500',
        'f-focus:ring-opacity-50'
    ],
    'light' => [
        'f-bg-gray-100',
        'f-border-gray-300',
        'f-text-gray-700',
        'f-hover:bg-gray-200',
        'f-hover:border-gray-400',
        'f-focus:ring-2',
        'f-focus:ring-gray-500',
        'f-focus:ring-opacity-50'
    ],
    'dark' => [
        'f-bg-gray-800',
        'f-border-gray-800',
        'f-text-white',
        'f-hover:bg-gray-900',
        'f-hover:border-gray-900',
        'f-focus:ring-2',
        'f-focus:ring-gray-700',
        'f-focus:ring-opacity-50'
    ]
];

// Build final CSS classes
$css_classes = array_merge(
    $base_classes,
    $size_classes[$size] ?? $size_classes['md'],
    $variant_classes[$variant] ?? $variant_classes['primary']
);

// Add disabled state
if ($disabled) {
    $css_classes = array_merge($css_classes, [
        'f-opacity-50',
        'f-cursor-not-allowed'
    ]);
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

if ($disabled) {
    $attrs['disabled'] = true;
    $attrs['aria-disabled'] = 'true';
}

if ($href): ?>
    <a href="<?= farme_escape($href) ?>" <?= farme_attributes($attrs) ?>>
        <?= farme_escape($text) ?>
    </a>
<?php else: ?>
    <button type="<?= farme_escape($type) ?>" <?= farme_attributes($attrs) ?>>
        <?= farme_escape($text) ?>
    </button>
<?php endif; ?>

