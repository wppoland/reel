<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container.
 *
 * @package Reel
 */

declare(strict_types=1);

use Plogins\Reel\Admin\Settings;
use Plogins\Reel\Container;
use Plogins\Reel\Frontend\VideoShortcode;
use Plogins\Reel\Migrator;
use Plogins\Reel\Service\ReelService;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());

    $c->singleton(ReelService::class, static fn (): ReelService => new ReelService());

    // Shortcode + dynamic block that place the featured video anywhere.
    $c->singleton(
        VideoShortcode::class,
        static fn (Container $c): VideoShortcode => new VideoShortcode($c->get(ReelService::class)),
    );

    // Admin (only needed in wp-admin context).
    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
    }
};
