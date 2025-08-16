<?php
/**
 * Typography Atom Component
 * 
 * Props:
 * - text: Text content
 * - element: h1|h2|h3|h4|h5|h6|p|span|div (default: p)
 * - size: xs|sm|md|lg|xl|2xl|3xl|4xl|5xl (default: md)
 * - weight: light|normal|medium|semibold|bold|extrabold (default: normal)
 * - color: CSS color value
 * - align: left|center|right|justify (default: left)
 * - transform: none|uppercase|lowercase|capitalize (default: none)
 * - decoration: none|underline|line-through (default: none)
 * - spacing: normal|tight|wide (default: normal)
 * - classes: additional CSS classes
 * - attributes: additional HTML attributes
 */

$text = $text ?? 'Typography';
$element = $element ?? 'p';
$size = $size ?? 'md';
$weight = $weight ?? 'normal';
$color = $color ?? '';
$align = $align ?? 'left';
$transform = $transform ?? 'none';
$decoration = $decoration ?? 'none';
$spacing = $spacing ?? 'normal';
$classes = $classes ?? '';
$attributes = $attributes ?? [];

// Validate element
$valid_elements = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div'];
if (!in_array($element, $valid_elements)) {
    $element = 'p';
}

// Build CSS classes
$css_classes = [
    'typography',
    'text-' . $size,
    'font-' . $weight,
    'text-' . $align,
    'text-transform-' . $transform,
    'text-decoration-' . $decoration,
    'letter-spacing-' . $spacing,
    $classes
];

$class_string = farme_classes($css_classes);

// Build styles
$styles = [];
if ($color) {
    $styles['color'] = $color;
}

$style_string = farme_styles($styles);

// Build attributes
$attrs = array_merge([
    'class' => $class_string,
    'style' => $style_string
], $attributes);
?>

<<?= $element ?> <?= farme_attributes($attrs) ?>>
    <?= farme_escape($text) ?>
</<?= $element ?>>

<style>
.typography {
    margin: 0;
    line-height: 1.5;
}

/* Font Sizes */
.text-xs {
    font-size: 0.75rem;
}

.text-sm {
    font-size: 0.875rem;
}

.text-md {
    font-size: 1rem;
}

.text-lg {
    font-size: 1.125rem;
}

.text-xl {
    font-size: 1.25rem;
}

.text-2xl {
    font-size: 1.5rem;
}

.text-3xl {
    font-size: 1.875rem;
}

.text-4xl {
    font-size: 2.25rem;
}

.text-5xl {
    font-size: 3rem;
}

/* Font Weights */
.font-light {
    font-weight: 300;
}

.font-normal {
    font-weight: 400;
}

.font-medium {
    font-weight: 500;
}

.font-semibold {
    font-weight: 600;
}

.font-bold {
    font-weight: 700;
}

.font-extrabold {
    font-weight: 800;
}

/* Text Alignment */
.text-left {
    text-align: left;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.text-justify {
    text-align: justify;
}

/* Text Transform */
.text-transform-none {
    text-transform: none;
}

.text-transform-uppercase {
    text-transform: uppercase;
}

.text-transform-lowercase {
    text-transform: lowercase;
}

.text-transform-capitalize {
    text-transform: capitalize;
}

/* Text Decoration */
.text-decoration-none {
    text-decoration: none;
}

.text-decoration-underline {
    text-decoration: underline;
}

.text-decoration-line-through {
    text-decoration: line-through;
}

/* Letter Spacing */
.letter-spacing-normal {
    letter-spacing: normal;
}

.letter-spacing-tight {
    letter-spacing: -0.025em;
}

.letter-spacing-wide {
    letter-spacing: 0.05em;
}

/* Heading Specific Styles */
.typography h1,
.typography.text-4xl,
.typography.text-5xl {
    line-height: 1.2;
    font-weight: 700;
}

.typography h2,
.typography.text-3xl {
    line-height: 1.3;
    font-weight: 600;
}

.typography h3,
.typography.text-2xl {
    line-height: 1.3;
    font-weight: 600;
}

.typography h4,
.typography.text-xl {
    line-height: 1.4;
    font-weight: 500;
}

.typography h5,
.typography h6,
.typography.text-lg {
    line-height: 1.4;
    font-weight: 500;
}

/* Responsive Typography */
@media (max-width: 768px) {
    .text-5xl {
        font-size: 2.25rem;
    }
    
    .text-4xl {
        font-size: 1.875rem;
    }
    
    .text-3xl {
        font-size: 1.5rem;
    }
}
</style>