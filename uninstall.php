<?php
/**
 * Uninstall cleanup. Runs when the plugin is deleted from the WordPress admin.
 * Removes the options Reel creates; product video meta is left untouched so a
 * reinstall (or Reel Pro) keeps existing per-product videos.
 *
 * @package Reel
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('reel_settings');
delete_option('reel_db_version');
