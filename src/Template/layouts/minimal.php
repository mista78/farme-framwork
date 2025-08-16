<!DOCTYPE html>
<html lang="<?= $locale ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= farme_escape($title ?? 'Farme Framework') ?></title>
    
    <?php if (isset($description)): ?>
    <meta name="description" content="<?= farme_escape($description) ?>">
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
</head>
<body class="f-font-sans f-leading-relaxed f-text-gray-800 f-max-w-4xl f-mx-auto f-py-5 f-px-4">
    <div class="f-prose f-prose-lg f-max-w-none">
        <?= $content ?>
    </div>
    
    <!-- FarmeDynamic JavaScript - Must load before other scripts -->
    <script src="/assets/js/farme-dynamic.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ((array)$additional_js as $js): ?>
            <?= farme_js($js) ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($custom_footer)): ?>
        <?= $custom_footer ?>
    <?php endif; ?>
    
    <style>
    /* Prose styles for minimal layout content */
    .f-prose h1, .f-prose h2, .f-prose h3, .f-prose h4, .f-prose h5, .f-prose h6 {
        margin-bottom: 1rem;
        font-weight: 600;
        line-height: 1.25;
    }
    
    .f-prose h1 { font-size: 2rem; }
    .f-prose h2 { font-size: 1.5rem; }
    .f-prose h3 { font-size: 1.25rem; }
    
    .f-prose p {
        margin-bottom: 1rem;
    }
    
    .f-prose a {
        color: #3b82f6;
        text-decoration: none;
    }
    
    .f-prose a:hover {
        text-decoration: underline;
    }
    
    .f-prose ul, .f-prose ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }
    
    .f-prose li {
        margin-bottom: 0.5rem;
    }
    </style>
</body>
</html>