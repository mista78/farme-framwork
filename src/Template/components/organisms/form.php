<?php
/**
 * Form Organism Component - Modern Home Page Style
 * 
 * Props:
 * - fields: Array of form fields (can be key => config or indexed array with 'name' key)
 * - submit_text: Submit button text (default: 'Submit')
 * - submit_classes: Additional classes for submit button
 * - method: POST|GET (default: POST)
 * - action: Form action URL
 * - classes: Additional CSS classes for form
 * - errors: Array of field errors
 * - csrf_token: CSRF token value
 */

$fields = $fields ?? [];
$submit_text = $submit_text ?? 'Submit';
$submit_classes = $submit_classes ?? '';
$method = strtoupper($method ?? 'POST');
$action = $action ?? '';
$classes = $classes ?? '';
$errors = $errors ?? [];
$csrf_token = $csrf_token ?? '';

// Normalize fields array to support both formats
$normalized_fields = [];
foreach ($fields as $key => $field) {
    if (is_string($key) && is_array($field)) {
        // Key-value format: 'email' => ['type' => 'email', ...]
        $field['name'] = $key;
        $normalized_fields[] = $field;
    } elseif (is_array($field) && isset($field['name'])) {
        // Array format: [['name' => 'email', 'type' => 'email'], ...]
        $normalized_fields[] = $field;
    }
}
?>

<form method="<?= $method ?>" action="<?= farme_escape($action) ?>" class="f-space-y-6 <?= farme_escape($classes) ?>">
    <?php if ($method === 'POST' && $csrf_token): ?>
        <input type="hidden" name="_token" value="<?= farme_escape($csrf_token) ?>">
    <?php endif; ?>
    
    <?php foreach ($normalized_fields as $field): ?>
        <?php
        $field_name = $field['name'] ?? '';
        $field_type = $field['type'] ?? 'text';
        $field_label = $field['label'] ?? '';
        $field_placeholder = $field['placeholder'] ?? $field_label;
        $field_value = $field['value'] ?? '';
        $field_required = $field['required'] ?? false;
        $field_error = $errors[$field_name] ?? '';
        ?>
        
        <?php if ($field_type === 'hidden'): ?>
            <input type="hidden" name="<?= farme_escape($field_name) ?>" value="<?= farme_escape($field_value) ?>">
        
        <?php elseif ($field_type === 'checkbox'): ?>
            <div>
                <label class="f-flex f-items-start f-text-sm f-text-gray-600">
                    <input type="checkbox" 
                           name="<?= farme_escape($field_name) ?>" 
                           value="<?= farme_escape($field['value'] ?? '1') ?>"
                           <?= $field_required ? 'required' : '' ?>
                           class="f-w-4 f-h-4 f-text-blue-600 f-border-gray-300 f-rounded f-focus:ring-blue-500 f-mt-0.5 f-mr-3">
                    <span>
                        <?= $field_label ?>
                    </span>
                </label>
                <?php if ($field_error): ?>
                    <div class="f-text-red-600 f-text-sm f-mt-1"><?= farme_escape($field_error) ?></div>
                <?php endif; ?>
            </div>
        
        <?php else: ?>
            <div>
                <input type="<?= farme_escape($field_type) ?>" 
                       name="<?= farme_escape($field_name) ?>" 
                       id="<?= farme_escape($field_name) ?>"
                       <?= $field_required ? 'required' : '' ?>
                       value="<?= farme_escape($field_value) ?>"
                       placeholder="<?= farme_escape($field_placeholder) ?>"
                       class="f-w-full f-px-6 f-py-4 f-border-2 f-border-gray-200 f-rounded-xl f-focus:ring-4 f-focus:ring-blue-500 f-focus:ring-opacity-20 f-focus:border-blue-500 f-text-lg f-bg-gray-50 f-focus:bg-white f-transition-all f-duration-300 f-shadow-sm f-hover:shadow-md <?= $field_error ? 'f-border-red-300 f-bg-red-50' : '' ?>">
                <?php if ($field_error): ?>
                    <div class="f-text-red-600 f-text-sm f-mt-2"><?= farme_escape($field_error) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <!-- Submit Button -->
    <div class="f-pt-2">
        <button type="submit" class="f-w-full f-bg-gradient-to-r f-from-blue-600 f-to-indigo-600 f-text-white f-py-4 f-px-6 f-rounded-xl f-text-lg f-font-bold f-hover:from-blue-700 f-hover:to-indigo-700 f-focus:outline-none f-focus:ring-4 f-focus:ring-blue-500 f-focus:ring-opacity-50 f-transition-all f-duration-300 f-transform f-hover:scale-105 f-active:scale-95 f-shadow-lg f-hover:shadow-xl <?= farme_escape($submit_classes) ?>">
            <?= farme_escape($submit_text) ?>
        </button>
    </div>
</form>

