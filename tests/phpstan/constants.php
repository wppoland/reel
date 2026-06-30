<?php
/**
 * Constants needed by PHPStan to analyse the plugin without bootstrapping WordPress.
 *
 * @package Reel
 */

declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) {
        define('ABSPATH', '/tmp/wordpress/');
    }
    if (! defined('REEL_DIR')) {
        define('REEL_DIR', '/tmp/reel/');
    }
    if (! defined('REEL_URL')) {
        define('REEL_URL', 'https://example.test/wp-content/plugins/reel/');
    }
}

namespace Reel {
    if (! defined('Reel\\VERSION')) {
        define('Reel\\VERSION', '0.1.0');
    }
    if (! defined('Reel\\PLUGIN_FILE')) {
        define('Reel\\PLUGIN_FILE', '/tmp/reel/reel.php');
    }
}
