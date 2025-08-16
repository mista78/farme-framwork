<!DOCTYPE html>
<html lang="<?= $locale ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title><?= farme_escape($title ?? 'Farme Framework') ?></title>
    
    <?php if (isset($description)): ?>
    <meta name="description" content="<?= farme_escape($description) ?>">
    <?php endif; ?>
    
    <?php if (isset($keywords)): ?>
    <meta name="keywords" content="<?= farme_escape($keywords) ?>">
    <?php endif; ?>
    
    <!-- FarmeDynamic CSS Base (JIT Generation) -->
    <link rel="stylesheet" href="/assets/css/farme-base.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ((array)$additional_css as $css): ?>
            <?= farme_css($css) ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($custom_head)): ?>
        <?= $custom_head ?>
    <?php endif; ?>
    
    <!-- Speculation Rules API for performance optimization -->
    <script type="speculationrules">
    {
        "prerender": [
            {
                "where": { "href_matches": "/*" },
                "eagerness": "moderate"
            }
        ],
        "prefetch": [
            {
                "where": { "href_matches": "/*" },
                "eagerness": "conservative"
            }
        ]
    }
    </script>
</head>
<body>
    <?php 
    // Prepare navigation data
    $default_navigation = [
        ['label' => 'Home', 'url' => '/', 'active' => false],
        ['label' => 'About', 'url' => '#about', 'active' => false],
        ['label' => 'Projects', 'url' => '#projects', 'active' => false],
        ['label' => 'Services', 'url' => '#services', 'active' => false],
        ['label' => 'Contact', 'url' => '#contact', 'active' => false],
    ];
    
    // Add custom nav links if provided
    if (isset($nav_links)) {
        foreach ($nav_links as $label => $url) {
            $default_navigation[] = ['label' => $label, 'url' => $url, 'active' => false];
        }
    }
    
    // Header organism props
    $header_props = [
        'title' => $site_title ?? farme_config_get('app.name', 'Farme Framework'),
        'navigation' => $header_navigation ?? $default_navigation,
        'logo' => $logo ?? '',
        'user' => $user ?? null,
        'search' => $search_enabled ?? false,
        'theme' => $header_theme ?? 'light',
        'classes' => $header_classes ?? ''
    ];
    
    ?>

    <!-- Header with FarmeCSS -->
    <header class="f-bg-white f-border-b f-border-gray-200 f-shadow-sm">
        <div class="f-max-w-7xl f-mx-auto f-px-4">
            <div class="f-flex f-justify-between f-items-center f-h-16">
                <!-- Logo -->
                <div class="f-flex f-items-center f-gap-3">
                    <div class="f-w-8 f-h-8 f-bg-gradient-to-br f-from-blue-500 f-to-purple-600 f-rounded f-flex f-items-center f-justify-center">
                        <span class="f-text-white f-font-bold f-text-sm">F</span>
                    </div>
                    <span class="f-font-bold f-text-xl f-text-gray-900"><?= farme_escape($site_title ?? 'Farme') ?></span>
                </div>
                
                <!-- Navigation -->
                <nav class="f-hidden f-md:flex f-items-center f-gap-6">
                    <?php foreach ($default_navigation as $item): ?>
                        <a href="<?= farme_url($item['url']) ?>" 
                           class="f-text-gray-700 f-hover:text-blue-600 f-transition-colors f-duration-200 f-font-medium">
                            <?= farme_escape($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= farme_url('components') ?>" class="f-text-gray-700 f-hover:text-blue-600 f-transition-colors f-duration-200 f-font-medium">Components</a>
                    <a href="<?= farme_url('admin') ?>" class="f-text-gray-700 f-hover:text-blue-600 f-transition-colors f-duration-200 f-font-medium">Admin Demo</a>
                </nav>
                
                <!-- Mobile menu button -->
                <button class="f-md:hidden f-p-2 f-text-gray-700">
                    <svg class="f-w-6 f-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="f-min-h-screen">
        <div>
            <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
                <?php foreach ($flash_messages as $type => $message): ?>
                    <div class="f-mb-4 f-p-4 f-rounded-lg f-border <?= $type === 'error' ? 'f-bg-red-50 f-border-red-200 f-text-red-800' : ($type === 'success' ? 'f-bg-green-50 f-border-green-200 f-text-green-800' : 'f-bg-blue-50 f-border-blue-200 f-text-blue-800') ?>">
                        <?= farme_escape($message) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?= $content ?>
        </div>
    </main>

    <!-- Footer with FarmeCSS -->
    <footer class="f-bg-gray-900 f-text-white f-py-8 f-mt-12">
        <div class="f-max-w-7xl f-mx-auto f-px-4">
            <div class="f-text-center">
                <p class="f-text-gray-300">
                    &copy; <?= date('Y') ?> <?= farme_escape($site_title ?? farme_config_get('app.name', 'Farme Framework')) ?>. 
                    Built with ❤️ using Farme Framework.
                </p>
                
                <div class="f-mt-4 f-flex f-justify-center f-items-center f-gap-4 f-text-sm">
                    <span class="f-text-gray-400">Powered by:</span>
                    <span class="f-bg-blue-600 f-px-2 f-py-1 f-rounded f-font-medium">FarmeCSS</span>
                    <span class="f-bg-green-600 f-px-2 f-py-1 f-rounded f-font-medium">FarmeDynamic</span>
                    <span class="f-bg-purple-600 f-px-2 f-py-1 f-rounded f-font-medium">FarmeJS</span>
                </div>
                
                <?php if (isset($footer_content)): ?>
                    <div class="f-mt-4">
                        <?= $footer_content ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <!-- FarmeDynamic JavaScript - Must load before other scripts -->
    <script src="/assets/js/farme-dynamic.js"></script>
    
    <!-- FarmeJS Framework -->
    <script src="/assets/js/farme.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ((array)$additional_js as $js): ?>
            <?= farme_js($js) ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($custom_footer)): ?>
        <?= $custom_footer ?>
    <?php endif; ?>
    
    <script>
        // Show dynamic CSS stats in console
        if (window.FarmeDynamic) {
            console.log('🎨 FarmeDynamic Active - CSS generated on-demand');
        }
    </script>
</body>
</html>