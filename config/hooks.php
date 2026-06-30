<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Reel\Contract\HasHooks.
 *
 * Admin-only classes are included only when running in wp-admin context.
 *
 * @package Reel
 *
 * @return array<class-string>
 */

declare(strict_types=1);

use Reel\Admin\Settings;
use Reel\Frontend\VideoShortcode;
use Reel\Service\ReelService;

defined('ABSPATH') || exit;

return is_admin()
    ? [
        ReelService::class,
        VideoShortcode::class,
        Settings::class,
    ]
    : [
        ReelService::class,
        VideoShortcode::class,
    ];
