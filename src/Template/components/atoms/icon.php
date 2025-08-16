<?php
/**
 * Icon Atom Component
 * 
 * Props:
 * - name: Icon name (using simple text icons or emojis)
 * - size: sm|md|lg|xl (default: md)
 * - color: CSS color value
 * - classes: additional CSS classes
 * - attributes: additional HTML attributes
 */

$name = $name ?? 'info';
$size = $size ?? 'md';
$color = $color ?? '';
$classes = $classes ?? '';
$attributes = $attributes ?? [];

// Icon mapping (using Unicode symbols and emojis)
$icons = [
    // Basic icons
    'info' => 'ℹ️',
    'warning' => '⚠️',
    'error' => '❌',
    'success' => '✅',
    'check' => '✓',
    'cross' => '✗',
    'plus' => '➕',
    'minus' => '➖',
    
    // Navigation
    'home' => '🏠',
    'back' => '←',
    'forward' => '→',
    'up' => '↑',
    'down' => '↓',
    'menu' => '☰',
    'close' => '✕',
    
    // Actions
    'edit' => '✏️',
    'delete' => '🗑️',
    'save' => '💾',
    'download' => '⬇️',
    'upload' => '⬆️',
    'search' => '🔍',
    'filter' => '🔽',
    'settings' => '⚙️',
    
    // Communication
    'mail' => '📧',
    'phone' => '📞',
    'message' => '💬',
    'notification' => '🔔',
    
    // Files
    'file' => '📄',
    'folder' => '📁',
    'image' => '🖼️',
    'video' => '🎥',
    'audio' => '🎵',
    
    // User
    'user' => '👤',
    'users' => '👥',
    'profile' => '👤',
    'login' => '🔐',
    'logout' => '🚪',
    
    // Time
    'calendar' => '📅',
    'clock' => '🕐',
    'timer' => '⏰',
    
    // Status
    'online' => '🟢',
    'offline' => '🔴',
    'loading' => '⏳',
    'lock' => '🔒',
    'unlock' => '🔓',
    
    // Misc
    'star' => '⭐',
    'heart' => '❤️',
    'thumbs-up' => '👍',
    'thumbs-down' => '👎',
    'fire' => '🔥',
    'lightning' => '⚡',
];

$icon_symbol = $icons[$name] ?? $name;

// Build CSS classes
$css_classes = [
    'icon',
    'icon-' . $size,
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
    'style' => $style_string,
    'aria-hidden' => 'true'
], $attributes);
?>

<span <?= farme_attributes($attrs) ?>>
    <?= $icon_symbol ?>
</span>

<style>
.icon {
    display: inline-block;
    line-height: 1;
    vertical-align: middle;
}

.icon-sm {
    font-size: 0.875rem;
}

.icon-md {
    font-size: 1rem;
}

.icon-lg {
    font-size: 1.25rem;
}

.icon-xl {
    font-size: 1.5rem;
}
</style>