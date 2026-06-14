<?php
/**
 * Plugin Name:       Reel - Gallery and Video for WooCommerce
 * Plugin URI:        https://plogins.com/reel/
 * Description:        Hover zoom, lightbox and featured video for the WooCommerce product gallery — reserved space, no jQuery
 * Version:           0.2.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            WPPoland
 * Author URI:        https://plogins.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       reel
 * Domain Path:       /languages
 * WC requires at least: 8.0
 *
 * @package Reel
 */

declare(strict_types=1);

namespace Reel;

defined('ABSPATH') || exit;

const VERSION     = '0.2.0';
const PLUGIN_FILE = __FILE__;

define('REEL_DIR', plugin_dir_path(__FILE__));
define('REEL_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/autoload.php';

// Translations: WordPress.org auto-loads them from the plugin slug since WP 4.6,
// so no load_plugin_textdomain() call is needed. Domain Path (/languages) and
// the bundled languages/reel.pot template support translators and self-hosting.

// Add a "Settings" link on the plugins list row.
add_filter('plugin_action_links_' . plugin_basename(__FILE__), static function (array $links): array {
    $settings = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('admin.php?page=reel-settings')),
        esc_html__('Settings', 'reel'),
    );

    array_unshift($links, $settings);

    return $links;
});

// HPOS + cart/checkout blocks compatibility.
add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('plugins_loaded', static function (): void {
    if (! class_exists('WooCommerce')) {
        add_action('admin_notices', static function (): void {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Reel requires WooCommerce to be active.', 'reel');
            echo '</p></div>';
        });
        return;
    }

    add_action('init', static function (): void {
        Plugin::instance()->boot();
    }, 0);
}, 10);
