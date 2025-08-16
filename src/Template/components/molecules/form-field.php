<?php
/**
 * Form Field Molecule Component
 * 
 * Combines label + input + error message
 * 
 * Props:
 * - label: Label text
 * - name: Input name
 * - type: Input type (default: text)
 * - value: Input value
 * - placeholder: Placeholder text
 * - required: Boolean
 * - disabled: Boolean
 * - error: Error message
 * - help: Help text
 * - classes: Additional CSS classes
 * - input_classes: Classes for input only
 * - label_classes: Classes for label only
 */

$label = $label ?? '';
$name = $name ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$disabled = $disabled ?? false;
$error = $error ?? '';
$help = $help ?? '';
$classes = $classes ?? '';
$input_classes = $input_classes ?? '';
$label_classes = $label_classes ?? '';

$input_id = $name ? 'input_' . $name : '';
$has_error = !empty($error);

// Build CSS classes
$field_classes = [
    'form-field',
    $has_error ? 'has-error' : '',
    $disabled ? 'disabled' : '',
    $classes
];

$field_class_string = farme_classes($field_classes);
?>

<div class="<?= $field_class_string ?>">
    <?php if ($label): ?>
        <?= farme_atom('label', [
            'text' => $label,
            'for' => $input_id,
            'required' => $required,
            'classes' => $label_classes
        ]) ?>
    <?php endif; ?>
    
    <?= farme_atom('input', [
        'type' => $type,
        'name' => $name,
        'id' => $input_id,
        'value' => $value,
        'placeholder' => $placeholder,
        'required' => $required,
        'disabled' => $disabled,
        'classes' => $input_classes . ($has_error ? ' is-invalid' : '')
    ]) ?>
    
    <?php if ($error): ?>
        <div class="field-error" role="alert">
            <?= farme_atom('icon', ['name' => 'error', 'size' => 'sm']) ?>
            <?= farme_escape($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($help && !$error): ?>
        <div class="field-help">
            <?= farme_escape($help) ?>
        </div>
    <?php endif; ?>
</div>

<style>
.form-field {
    margin-bottom: 1rem;
}

.form-field.disabled {
    opacity: 0.6;
}

.field-error {
    display: flex;
    align-items: center;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

.field-error .icon {
    margin-right: 0.25rem;
}

.field-help {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.form-field.has-error .form-input {
    border-color: #dc3545;
}

.form-field.has-error .form-input:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.125rem rgba(220, 53, 69, 0.25);
}
</style>