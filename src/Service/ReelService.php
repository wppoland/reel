<?php

declare(strict_types=1);

namespace Reel\Service;

use Reel\Contract\HasHooks;
use WPPoland\StorefrontKit\Media\FeaturedVideoEngine;
use WPPoland\StorefrontKit\Media\GalleryZoomEngine;

defined('ABSPATH') || exit;

/**
 * Wires {@see GalleryZoomEngine} and {@see FeaturedVideoEngine} with this
 * plugin's text-domain ('reel'), option prefix ('reel_'), asset URLs and
 * product meta keys. This class supplies localisation, option storage, asset
 * URLs and template rendering.
 *
 * The zoom/lightbox/video CSS+JS ship in this plugin (assets/), enqueued with
 * the no-jQuery, deferred, in-footer convention.
 */
final class ReelService implements HasHooks
{
    private const OPTION = 'reel_settings';

    /** Product meta keys for the featured video. */
    private const META_VIDEO_URL   = '_reel_video_url';
    private const META_VIDEO_TITLE = '_reel_video_title';

    private ?GalleryZoomEngine $zoom = null;

    private ?FeaturedVideoEngine $video = null;

    public function videoEngine(): ?FeaturedVideoEngine
    {
        return $this->video;
    }

    public function __construct()
    {
        // When the media engines are available, wire them with this plugin's
        // text-domain / option prefix / assets. Otherwise leave the service inert
        // (see registerHooks()).
        if (class_exists(GalleryZoomEngine::class)) {
            $this->zoom = new GalleryZoomEngine(
                'reelGalleryZoom',
                'reel-gallery-zoom',
                REEL_URL . 'assets/css/gallery-zoom.css',
                REEL_URL . 'assets/js/gallery-zoom.js',
                \Reel\VERSION,
                'lightbox',
                ['trigger' => __('Open image in full screen', 'reel')],
                fn (): bool => $this->zoomEnabled(),
                static fn (): bool => function_exists('is_product') && is_product(),
                fn (): array => $this->zoomSettings(),
                function (string $template, array $context): void {
                    $this->renderTemplate($template, $context);
                },
            );
        }

        if (class_exists(FeaturedVideoEngine::class)) {
            $this->video = new FeaturedVideoEngine(
                'reel-featured-video',
                REEL_URL . 'assets/css/featured-video.css',
                \Reel\VERSION,
                'featured-video',
                ['url' => self::META_VIDEO_URL, 'title' => self::META_VIDEO_TITLE],
                ['title' => __('Product video', 'reel')],
                fn (): bool => $this->videoEnabled(),
                static fn (): bool => function_exists('is_product') && is_product(),
                fn (): array => $this->videoSettings(),
                static fn (\WC_Product $product, string $key): mixed => $product->get_meta($key),
                function (string $template, array $context): void {
                    $this->renderTemplate($template, $context);
                },
            );
        }
    }

    public function registerHooks(): void
    {
        $registered = false;

        if ($this->zoom instanceof GalleryZoomEngine) {
            $this->zoom->registerHooks();
            $registered = true;
        }

        if ($this->video instanceof FeaturedVideoEngine) {
            $this->video->registerHooks();
            $registered = true;
        }

        if (! $registered) {
            // The media engines are unavailable; no hooks run until present.
            return;
        }

        // The engine localizes a fixed config set; inject the plugin-local zoom
        // extras (touch + caption) as a second config object on the same handle,
        // after the engine has enqueued it on single product pages.
        if ($this->zoom instanceof GalleryZoomEngine) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueZoomExtras'], 20);
        }
    }

    /**
     * Attach plugin-local zoom config (touch + caption) to the gallery
     * script. Runs after the engine's own enqueue (priority 20 vs default 10).
     */
    public function enqueueZoomExtras(): void
    {
        if (! wp_script_is('reel-gallery-zoom', 'enqueued')) {
            return;
        }

        $settings = $this->settings();

        $extra = wp_json_encode([
            'disableZoomOnTouch' => (bool) ($settings['disable_zoom_on_touch'] ?? true),
            'lightboxCaption'    => (bool) ($settings['lightbox_caption'] ?? false),
        ]);

        if (! is_string($extra)) {
            return;
        }

        wp_add_inline_script(
            'reel-gallery-zoom',
            'window.reelGalleryExtra = ' . $extra . ';',
            'before',
        );
    }

    private function zoomEnabled(): bool
    {
        $settings = $this->settings();

        return (bool) ($settings['enable_zoom'] ?? false)
            || (bool) ($settings['enable_lightbox'] ?? false);
    }

    private function videoEnabled(): bool
    {
        return (bool) ($this->settings()['enable_video'] ?? false);
    }

    /**
     * Settings shaped for GalleryZoomEngine's localized config.
     *
     * @return array<string, mixed>
     */
    private function zoomSettings(): array
    {
        $settings = $this->settings();

        $triggerLabel = trim((string) ($settings['trigger_label'] ?? ''));

        $config = [
            'enable_zoom'           => (bool) ($settings['enable_zoom'] ?? true),
            'enable_lightbox'       => (bool) ($settings['enable_lightbox'] ?? true),
            'zoom_scale'            => (float) ($settings['zoom_scale'] ?? 1.45),
            'show_backdrop_close'   => (bool) ($settings['show_backdrop_close'] ?? true),
            'disable_zoom_on_touch' => (bool) ($settings['disable_zoom_on_touch'] ?? true),
            'lightbox_caption'      => (bool) ($settings['lightbox_caption'] ?? false),
        ];

        // Only override the engine fallback when an explicit label is set.
        if ($triggerLabel !== '') {
            $config['trigger_label'] = $triggerLabel;
        }

        return $config;
    }

    /**
     * Settings shaped for FeaturedVideoEngine.
     *
     * @return array<string, mixed>
     */
    private function videoSettings(): array
    {
        $settings = $this->settings();

        $intro = trim((string) ($settings['video_intro'] ?? ''));

        return [
            'position'       => (string) ($settings['video_position'] ?? 'after_gallery'),
            'autoplay'       => (bool) ($settings['video_autoplay'] ?? false),
            'show_title'     => (bool) ($settings['video_show_title'] ?? true),
            'title'          => trim((string) ($settings['video_title'] ?? '')),
            'intro_text'     => $intro,
            'show_intro'     => $intro !== '',
            'show_on_single' => true,
        ];
    }

    /**
     * Build the featured-video markup for a given product, for the shortcode /
     * block. Returns an empty string when video is disabled or the product has
     * no video URL. Mirrors the engine's own enable + meta resolution.
     */
    public function renderVideoHtml(\WC_Product $product): string
    {
        if (! $this->videoEnabled() || ! $this->video instanceof FeaturedVideoEngine) {
            return '';
        }

        return $this->video->getVideoHtml($product);
    }

    /**
     * Heading text for the featured video of a product: the per-product title
     * meta, then the configured default, then the engine label fallback.
     */
    public function videoTitleFor(\WC_Product $product): string
    {
        $title = trim((string) $product->get_meta(self::META_VIDEO_TITLE));

        if ($title !== '') {
            return $title;
        }

        $settings = $this->settings();
        $default  = trim((string) ($settings['video_title'] ?? ''));

        return $default !== '' ? $default : __('Product video', 'reel');
    }

    /**
     * Stored settings merged over packaged defaults.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require REEL_DIR . 'config/defaults.php';

        return array_merge($defaults, $stored);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderTemplate(string $template, array $context): void
    {
        $file = REEL_DIR . 'templates/' . $template . '.php';

        if (! is_readable($file)) {
            return;
        }

        extract($context, EXTR_SKIP);
        require $file;
    }
}
