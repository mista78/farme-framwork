<?php
/**
 * Alert Molecule Component
 * 
 * Props:
 * - message: Alert message
 * - title: Optional alert title
 * - variant: success|info|warning|danger (default: info)
 * - dismissible: Boolean - shows close button
 * - icon: Boolean - shows icon (default: true)
 * - classes: Additional CSS classes
 */

$message = $message ?? '';
$title = $title ?? '';
$variant = $variant ?? 'info';
$dismissible = $dismissible ?? false;
$icon = $icon ?? true;
$classes = $classes ?? '';

// Icon mapping for variants
$variant_icons = [
    'success' => 'success',
    'info' => 'info',
    'warning' => 'warning',
    'danger' => 'error'
];

$alert_icon = $variant_icons[$variant] ?? 'info';

?>

<?php
// Color variants for FarmeCSS
$variant_classes = [
    'success' => ['f-bg-green-50', 'f-border-green-200', 'f-text-green-800'],
    'info' => ['f-bg-blue-50', 'f-border-blue-200', 'f-text-blue-800'],
    'warning' => ['f-bg-yellow-50', 'f-border-yellow-200', 'f-text-yellow-800'],
    'danger' => ['f-bg-red-50', 'f-border-red-200', 'f-text-red-800']
];

$alert_colors = $variant_classes[$variant] ?? $variant_classes['info'];
?>

<div class="f-relative f-p-4 f-mb-4 f-border f-rounded-lg <?= implode(' ', $alert_colors) ?> <?= $classes ?>" role="alert">
    <div class="f-flex f-items-start f-gap-3">
        <?php if ($icon): ?>
            <div class="f-flex-shrink-0 f-mt-0.5">
                <svg class="f-w-5 f-h-5" fill="currentColor" viewBox="0 0 20 20">
                    <?php if ($variant === 'success'): ?>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    <?php elseif ($variant === 'warning'): ?>
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    <?php elseif ($variant === 'danger'): ?>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    <?php else: // info ?>
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    <?php endif; ?>
                </svg>
            </div>
        <?php endif; ?>
        
        <div class="f-flex-1">
            <?php if ($title): ?>
                <h6 class="f-text-base f-font-semibold f-mb-1"><?= farme_escape($title) ?></h6>
            <?php endif; ?>
            
            <div class="f-text-sm f-leading-relaxed">
                <?= farme_escape($message) ?>
            </div>
        </div>
    </div>
    
    <?php if ($dismissible): ?>
        <button type="button" class="f-absolute f-top-2 f-right-2 f-p-1 f-rounded f-hover:bg-black f-hover:bg-opacity-10 f-transition-colors" aria-label="Close alert" onclick="this.parentElement.style.display='none'">
            <svg class="f-w-4 f-h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    <?php endif; ?>
</div>

