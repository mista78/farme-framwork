<?php
/**
 * Header Organism Component
 * 
 * Props:
 * - title: Site title
 * - navigation: Array of navigation links [['label' => 'Home', 'url' => '/', 'active' => false]]
 * - logo: Logo URL
 * - user: Current user data
 * - search: Boolean - show search box
 * - theme: light|dark (default: light)
 * - classes: Additional CSS classes
 */

$title = $title ?? farme_config_get('app.name', 'Farme Framework');
$navigation = $navigation ?? [];
$logo = $logo ?? '';
$user = $user ?? null;
$search = $search ?? false;
$theme = $theme ?? 'light';
$classes = $classes ?? '';

// Build CSS classes
$header_classes = [
    'site-header',
    'header-' . $theme,
    $classes
];

$header_class_string = farme_classes($header_classes);
?>

<header class="<?= $header_class_string ?>">
    <div class="header-container">
        <!-- Brand/Logo -->
        <div class="header-brand">
            <?php if ($logo): ?>
                <a href="<?= farme_url() ?>" class="brand-logo">
                    <img src="<?= farme_escape($logo) ?>" alt="<?= farme_escape($title) ?>" class="logo-img">
                </a>
            <?php endif; ?>
            <a href="<?= farme_url() ?>" class="brand-title">
                <h1><?= farme_escape($title) ?></h1>
            </a>
        </div>
        
        <!-- Navigation -->
        <?php if (!empty($navigation)): ?>
            <nav class="header-nav" role="navigation">
                <ul class="nav-list">
                    <?php foreach ($navigation as $nav_item): ?>
                        <li class="nav-item">
                            <?= farme_atom('button', [
                                'text' => $nav_item['label'] ?? '',
                                'href' => farme_url($nav_item['url'] ?? ''),
                                'variant' => ($nav_item['active'] ?? false) ? 'primary' : 'light',
                                'size' => 'sm',
                                'classes' => 'nav-link'
                            ]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Search -->
            <?php if ($search): ?>
                <div class="header-search">
                    <?= farme_molecule('form-field', [
                        'type' => 'search',
                        'name' => 'search',
                        'placeholder' => 'Search...',
                        'input_classes' => 'search-input'
                    ]) ?>
                </div>
            <?php endif; ?>
            
            <!-- User Menu -->
            <?php if ($user): ?>
                <div class="header-user">
                    <div class="user-info">
                        <?= farme_atom('icon', ['name' => 'user', 'size' => 'md']) ?>
                        <span class="user-name"><?= farme_escape($user['name'] ?? 'User') ?></span>
                    </div>
                    <div class="user-menu">
                        <?= farme_atom('button', [
                            'text' => 'Profile',
                            'href' => farme_url('profile'),
                            'variant' => 'light',
                            'size' => 'sm'
                        ]) ?>
                        <?= farme_atom('button', [
                            'text' => 'Logout',
                            'href' => farme_url('logout'),
                            'variant' => 'secondary',
                            'size' => 'sm'
                        ]) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="header-auth">
                    <?= farme_atom('button', [
                        'text' => 'Login',
                        'href' => farme_url('login'),
                        'variant' => 'light',
                        'size' => 'sm'
                    ]) ?>
                    <?= farme_atom('button', [
                        'text' => 'Sign Up',
                        'href' => farme_url('register'),
                        'variant' => 'primary',
                        'size' => 'sm'
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" aria-label="Toggle navigation">
            <?= farme_atom('icon', ['name' => 'menu', 'size' => 'lg']) ?>
        </button>
    </div>
</header>

<style>
.site-header {
    background-color: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.header-dark {
    background-color: #343a40;
    border-bottom-color: #495057;
    color: #fff;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.brand-logo .logo-img {
    height: 2rem;
    width: auto;
}

.brand-title h1 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: inherit;
    text-decoration: none;
}

.brand-title:hover {
    text-decoration: none;
    color: inherit;
}

.header-nav {
    flex: 1;
    display: flex;
    justify-content: center;
}

.nav-list {
    display: flex;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.nav-item {
    display: flex;
}

.nav-link {
    white-space: nowrap;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-search {
    min-width: 200px;
}

.header-search .form-field {
    margin-bottom: 0;
}

.search-input {
    background-color: #f8f9fa;
    border-color: transparent;
}

.search-input:focus {
    background-color: #fff;
    border-color: #80bdff;
}

.header-user,
.header-auth {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
}

.header-dark .user-info {
    background-color: #495057;
    color: #fff;
}

.user-name {
    font-weight: 500;
    white-space: nowrap;
}

.user-menu {
    display: flex;
    gap: 0.25rem;
}

.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-nav {
        display: none;
    }
    
    .header-search {
        display: none;
    }
    
    .mobile-menu-toggle {
        display: block;
    }
    
    .user-name {
        display: none;
    }
    
    .header-actions {
        gap: 0.5rem;
    }
}
</style>