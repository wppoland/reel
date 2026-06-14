<?php
/**
 * Default settings, merged under the option key `reel_settings`.
 *
 * The plugin ships with both features enabled; the merchant tunes them from the
 * Reel admin screen. The featured video shows per-product when a video URL is
 * stored in product meta.
 *
 * @package Reel
 *
 * @return array{enable_zoom:bool,enable_lightbox:bool,zoom_scale:float,show_backdrop_close:bool,disable_zoom_on_touch:bool,lightbox_caption:bool,trigger_label:string,enable_video:bool,video_position:string,video_autoplay:bool,video_show_title:bool,video_title:string,video_intro:string}
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    // Gallery zoom + lightbox.
    'enable_zoom'           => true,
    'enable_lightbox'       => true,
    'zoom_scale'            => 1.45,
    'show_backdrop_close'   => true,
    'disable_zoom_on_touch' => true,
    'lightbox_caption'      => false,
    'trigger_label'         => '',

    // Featured video.
    'enable_video'          => true,
    'video_position'        => 'after_gallery',
    'video_autoplay'        => false,
    'video_show_title'      => true,
    'video_title'           => '',
    'video_intro'           => '',
];
