<?php
/**
 * Card Molecule Component - FarmeCSS Version
 * 
 * Props:
 * - title: Card title
 * - content: Card content
 * - image: Image URL for card header
 * - actions: Array of action buttons
 * - variant: default|outlined|elevated (default: default)
 * - classes: Additional CSS classes
 */

$title = $title ?? '';
$content = $content ?? '';
$image = $image ?? '';
$actions = $actions ?? [];
$variant = $variant ?? 'default';
$classes = $classes ?? '';

// Base card classes using FarmeCSS utilities
$base_classes = [
    'f-bg-white',
    'f-rounded-lg',
    'f-overflow-hidden',
    'f-mb-4'
];

// Variant-specific classes
$variant_classes = [
    'default' => [
        'f-border',
        'f-border-gray-200'
    ],
    'outlined' => [
        'f-border-2',
        'f-border-gray-300'
    ],
    'elevated' => [
        'f-border',
        'f-border-gray-200',
        'f-shadow-lg',
        'f-hover:shadow-xl',
        'f-hover:-translate-y-1',
        'f-transition-all',
        'f-duration-300'
    ]
];

// Build final CSS classes
$card_classes = array_merge(
    $base_classes,
    $variant_classes[$variant] ?? $variant_classes['default']
);

// Add custom classes
if ($classes) {
    if (is_array($classes)) {
        $card_classes = array_merge($card_classes, $classes);
    } else {
        $card_classes[] = $classes;
    }
}

$card_class_string = implode(' ', array_filter($card_classes));
?>

<div class="<?= $card_class_string ?>">
    <?php if ($image): ?>
        <div class="f-relative f-overflow-hidden">
            <img src="<?= farme_escape($image) ?>" 
                 alt="<?= farme_escape($title) ?>" 
                 class="f-w-full f-h-48 f-object-cover f-block">
        </div>
    <?php endif; ?>
    
    <div class="f-p-5">
        <?php if ($title): ?>
            <h5 class="f-text-xl f-font-semibold f-text-gray-900 f-mb-3">
                <?= farme_escape($title) ?>
            </h5>
        <?php endif; ?>
        
        <?php if ($content): ?>
            <div class="f-text-gray-600 f-mb-4 f-leading-relaxed">
                <?= $content ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($actions)): ?>
            <div class="f-flex f-gap-2 f-flex-wrap">
                <?php foreach ($actions as $action): ?>
                    <?php if (is_array($action)): ?>
                        <?= farme_atom('button', $action) ?>
                    <?php else: ?>
                        <?= $action ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

