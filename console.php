<?php

/**
 * Farme Framework Console Entry Point Template
 */

// Define global path constants
define('ROOT_PATH', __DIR__);
define('WEBROOT_PATH', __DIR__ . '/webroot');
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOG_PATH', ROOT_PATH . '/logs');
define('TEMPLATES_PATH', SRC_PATH . '/Template');
define('CONTROLLERS_PATH', SRC_PATH . '/Controller');
define('MODELS_PATH', SRC_PATH . '/Model');
define('CORE_PATH', ROOT_PATH . '/vendor/farme/framework/src/Core');

// Include framework bootstrap
require_once ROOT_PATH . '/vendor/farme/framework/src/bootstrap.php';

// Boot console application
farme_boot_console($argv);