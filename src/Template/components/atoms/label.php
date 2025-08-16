<?php
/**
 * Label Atom Component
 * 
 * Props:
 * - text: Label text
 * - for: associated input id
 * - required: boolean - shows asterisk
 * - classes: additional CSS classes
 * - attributes: additional HTML attributes
 */

$text = $text ?? 'Label';
$for = $for ?? '';
$required = $required ?? false;
$classes = $classes ?? '';
$attributes = $attributes ?? [];

// Build CSS classes
$css_classes = [
    'form-label',
    $required ? 'required' : '',
    $classes
];

$class_string = farme_classes($css_classes);

// Build attributes
$attrs = array_merge([
    'class' => $class_string,
    'for' => $for
], $attributes);
?>

<label <?= farme_attributes($attrs) ?>>
    <?= farme_escape($text) ?>
    <?php if ($required): ?>
        <span class="required-asterisk" aria-label="required">*</span>
    <?php endif; ?>
</label>

<style>
.form-label {
    display: inline-block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #212529;
}

.form-label.required .required-asterisk {
    color: #dc3545;
    margin-left: 0.25rem;
}

.form-label:empty {
    display: none;
}
</style>