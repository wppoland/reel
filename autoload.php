<?php
/**
 * Autoloading: prefer Composer's vendor autoloader (the optimized classmap).
 * Fall back to a minimal PSR-4 autoloader so the plugin still boots if vendor/
 * is somehow absent.
 *
 * @package Reel
 */

declare(strict_types=1);

namespace Reel;

defined('ABSPATH') || exit;

$reel_composer = __DIR__ . '/vendor/autoload.php';
if (is_readable($reel_composer)) {
    require_once $reel_composer;
    return;
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Reel\\'           => __DIR__ . '/src/',
        'WPPoland\\StorefrontKit\\'    => __DIR__ . '/vendor/wppoland/storefront-kit/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relative = substr($class, $len);
        $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($file)) {
            require_once $file;
        }
        return;
    }
});
