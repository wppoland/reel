<?php

declare(strict_types=1);

namespace Plogins\Reel\Admin;

defined('ABSPATH') || exit;

use Plogins\Reel\Contract\HasHooks;

/**
 * Admin settings page registered as a top-level "Reel" menu.
 *
 * Stores settings in the `reel_settings` option (array): feature toggles for
 * gallery zoom, lightbox and featured video plus their tuning. All output is
 * escaped; all input sanitised and clamped on save.
 */
final class Settings implements HasHooks
{
    private const OPTION = 'reel_settings';
    private const PAGE   = 'reel-settings';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue the settings-page CSS/JS as real files (Plugin-Check clean),
     * only on the Reel screen. JS is deferred and loaded in the footer.
     */
    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . self::PAGE) {
            return;
        }

        wp_enqueue_style(
            'reel-admin',
            REEL_URL . 'assets/css/admin.css',
            [],
            \Plogins\Reel\VERSION,
        );

        wp_enqueue_script(
            'reel-admin',
            REEL_URL . 'assets/js/admin.js',
            [],
            \Plogins\Reel\VERSION,
            ['strategy' => 'defer', 'in_footer' => true],
        );
    }

    /**
     * Render an accessible "?" help affordance with a tooltip bubble.
     *
     * The button is keyboard-focusable and toggles aria-expanded (JS); the
     * bubble is exposed to assistive tech via aria-describedby and role=tooltip,
     * and shows on hover/focus via CSS even with JS disabled.
     */
    private function help(string $text, string $id): string
    {
        return sprintf(
            '<span class="reel-help"><button type="button" class="reel-help__btn" aria-describedby="%1$s" aria-label="%3$s">?</button><span class="reel-help__bubble" id="%1$s" role="tooltip">%2$s</span></span>',
            esc_attr($id),
            esc_html($text),
            esc_attr__('More information', 'plogins-reel'),
        );
    }

    public function addMenuPage(): void
    {
        add_menu_page(
            __('Reel Settings', 'plogins-reel'),
            __('Reel', 'plogins-reel'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
            'dashicons-format-video',
            58,
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        // The menu uses manage_woocommerce; align the options.php save capability
        // so shop managers (not just admins with manage_options) can save.
        add_filter(
            'option_page_capability_' . self::PAGE,
            static fn (): string => 'manage_woocommerce',
        );
    }

    /**
     * Render a styled toggle switch backed by a native checkbox (so it stays
     * keyboard- and screen-reader-native). Output is fully escaped.
     */
    private function toggle(string $key, bool $checked, string $label): void
    {
        printf(
            '<label class="reel-toggle"><input type="checkbox" name="%1$s[%2$s]" value="1"%3$s /><span class="reel-toggle__track" aria-hidden="true"></span><span class="reel-toggle__text">%4$s</span></label>',
            esc_attr(self::OPTION),
            esc_attr($key),
            checked($checked, true, false),
            esc_html($label),
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $s = $this->settings();
        $o = self::OPTION;

        // Allowed HTML for the help affordance markup echoed below.
        $help_kses = [
            'span'   => ['class' => true, 'id' => true, 'role' => true],
            'button' => ['type' => true, 'class' => true, 'aria-describedby' => true, 'aria-label' => true],
        ];
        ?>
        <div class="wrap reel-admin">
            <div class="reel-admin__hero">
                <span class="reel-admin__hero-icon" aria-hidden="true">
                    <span class="dashicons dashicons-format-video"></span>
                </span>
                <div class="reel-admin__hero-text">
                    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <p><?php esc_html_e('Hover zoom, an accessible lightbox and a featured product video for your WooCommerce gallery, tuned for speed, with no layout shift and no jQuery.', 'plogins-reel'); ?></p>
                </div>
                <div class="reel-admin__hero-badges">
                    <span class="reel-admin__badge"><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php esc_html_e('No CLS', 'plogins-reel'); ?></span>
                    <span class="reel-admin__badge"><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php esc_html_e('No jQuery', 'plogins-reel'); ?></span>
                    <span class="reel-admin__badge"><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php esc_html_e('Accessible', 'plogins-reel'); ?></span>
                </div>
            </div>

            <form method="post" action="options.php" class="reel-admin__form">
                <?php settings_fields(self::PAGE); ?>

                <div class="reel-admin__grid">
                    <section class="reel-admin__card">
                        <div class="reel-admin__card-head">
                            <span class="dashicons dashicons-search" aria-hidden="true"></span>
                            <div>
                                <h2><?php esc_html_e('Gallery zoom & lightbox', 'plogins-reel'); ?></h2>
                                <p><?php esc_html_e('How shoppers explore your product images: magnify on hover and open them full-screen.', 'plogins-reel'); ?></p>
                            </div>
                        </div>
                        <div class="reel-admin__card-body">

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Hover zoom', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('When a shopper hovers a gallery image, it magnifies in place to reveal detail, great for textures, labels and fine print. The zoom stays inside the image frame, so nothing on the page moves.', 'plogins-reel'), 'reel-help-zoom'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('enable_zoom', (bool) $s['enable_zoom'], __('Magnify the image on hover', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <label for="reel-zoom-scale"><?php esc_html_e('Zoom strength', 'plogins-reel'); ?></label>
                                    <?php echo wp_kses($this->help(__('How much the image grows on hover. 1.2× is subtle; 2× is dramatic. Around 1.4–1.6× reads well for most stores.', 'plogins-reel'), 'reel-help-scale'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control reel-admin__range">
                                    <input type="range" min="1" max="3" step="0.05" value="<?php echo esc_attr((string) $s['zoom_scale']); ?>" data-reel-zoom-range aria-hidden="true" tabindex="-1" />
                                    <input type="number" id="reel-zoom-scale" min="1" max="3" step="0.05" class="small-text" name="<?php echo esc_attr($o); ?>[zoom_scale]" value="<?php echo esc_attr((string) $s['zoom_scale']); ?>" data-reel-zoom-number />
                                    <output data-reel-zoom-output aria-hidden="true"></output>
                                </div>
                                <p class="reel-field__hint"><?php esc_html_e('Magnification factor on hover, from 1.0× (off) to 3.0×.', 'plogins-reel'); ?></p>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Disable zoom on touch', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Phones and tablets have no real hover, so the zoom can feel unpredictable. Keep this on to skip it on touch devices and rely on the lightbox there instead.', 'plogins-reel'), 'reel-help-touch'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('disable_zoom_on_touch', (bool) $s['disable_zoom_on_touch'], __('Skip hover zoom on touch devices', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Lightbox', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Clicking a gallery image opens it full-screen over a dimmed backdrop. Fully keyboard-operable: open with Enter/Space, close with Escape, and focus returns to where it was.', 'plogins-reel'), 'reel-help-lightbox'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('enable_lightbox', (bool) $s['enable_lightbox'], __('Open images full-screen on click', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Close on backdrop click', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Let shoppers dismiss the lightbox by clicking the dark area around the image, in addition to the close button and Escape key.', 'plogins-reel'), 'reel-help-backdrop'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('show_backdrop_close', (bool) $s['show_backdrop_close'], __('Click outside the image to close', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Image caption', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Show each image\'s alt text as a caption inside the lightbox. Helpful when your images have descriptive alt text; leave off if they don\'t.', 'plogins-reel'), 'reel-help-caption'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('lightbox_caption', (bool) $s['lightbox_caption'], __('Show the image alt text as a caption', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <label for="reel-trigger-label"><?php esc_html_e('Open-image label', 'plogins-reel'); ?></label>
                                    <?php echo wp_kses($this->help(__('The accessible label screen readers announce for each gallery image, e.g. "Open image in full screen". Leave empty to use the built-in default.', 'plogins-reel'), 'reel-help-trigger'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <input type="text" id="reel-trigger-label" class="regular-text" name="<?php echo esc_attr($o); ?>[trigger_label]" value="<?php echo esc_attr((string) $s['trigger_label']); ?>" placeholder="<?php esc_attr_e('Open image in full screen', 'plogins-reel'); ?>" />
                                </div>
                                <p class="reel-field__hint"><?php esc_html_e('Accessible label for the open-in-lightbox control. Leave empty for the default.', 'plogins-reel'); ?></p>
                            </div>

                        </div>
                    </section>

                    <section class="reel-admin__card">
                        <div class="reel-admin__card-head">
                            <span class="dashicons dashicons-format-video" aria-hidden="true"></span>
                            <div>
                                <h2><?php esc_html_e('Featured video', 'plogins-reel'); ?></h2>
                                <p>
                                    <?php
                                    $reel_meta = sprintf(
                                        /* translators: %s: the product meta field name. */
                                        __('Add a video to any product by setting its %s meta field to a self-hosted MP4/WebM file or a YouTube/Vimeo link.', 'plogins-reel'),
                                        '<code class="reel-admin__code">_reel_video_url</code>',
                                    );
                                    echo wp_kses($reel_meta, ['code' => ['class' => true]]);
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="reel-admin__card-body">

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Show featured video', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Master switch for the product video. When on, products that have a video URL show it on their single product page in the position you choose below.', 'plogins-reel'), 'reel-help-video'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('enable_video', (bool) $s['enable_video'], __('Display the product video on the product page', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <label for="reel-video-position"><?php esc_html_e('Position', 'plogins-reel'); ?></label>
                                    <?php echo wp_kses($this->help(__('Where the video appears: directly under the image gallery, or above the title/price summary column. Pick whichever fits your theme best.', 'plogins-reel'), 'reel-help-position'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <select id="reel-video-position" name="<?php echo esc_attr($o); ?>[video_position]">
                                        <option value="after_gallery" <?php selected((string) $s['video_position'], 'after_gallery'); ?>><?php esc_html_e('After the gallery', 'plogins-reel'); ?></option>
                                        <option value="before_summary" <?php selected((string) $s['video_position'], 'before_summary'); ?>><?php esc_html_e('Before the summary', 'plogins-reel'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Autoplay', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Start the video automatically on page load. Browsers only allow this when the video is muted, so sound stays off until the shopper turns it on. Use sparingly, it can be distracting.', 'plogins-reel'), 'reel-help-autoplay'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('video_autoplay', (bool) $s['video_autoplay'], __('Play automatically (muted)', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <?php esc_html_e('Show heading', 'plogins-reel'); ?>
                                    <?php echo wp_kses($this->help(__('Display a heading above the video. Turn off for a cleaner, heading-free embed.', 'plogins-reel'), 'reel-help-showtitle'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <?php $this->toggle('video_show_title', (bool) $s['video_show_title'], __('Show a heading above the video', 'plogins-reel')); ?>
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <label for="reel-video-title"><?php esc_html_e('Default heading', 'plogins-reel'); ?></label>
                                    <?php echo wp_kses($this->help(__('The heading used when a product has no video heading of its own. Leave empty to use the built-in "Product video" text.', 'plogins-reel'), 'reel-help-deftitle'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <input type="text" id="reel-video-title" class="regular-text" name="<?php echo esc_attr($o); ?>[video_title]" value="<?php echo esc_attr((string) $s['video_title']); ?>" placeholder="<?php esc_attr_e('Product video', 'plogins-reel'); ?>" />
                                </div>
                            </div>

                            <div class="reel-field">
                                <span class="reel-field__label">
                                    <label for="reel-video-intro"><?php esc_html_e('Intro text', 'plogins-reel'); ?></label>
                                    <?php echo wp_kses($this->help(__('An optional short paragraph shown under the heading, e.g. "See it in action." Leave empty to hide it.', 'plogins-reel'), 'reel-help-intro'), $help_kses); ?>
                                </span>
                                <div class="reel-field__control">
                                    <textarea id="reel-video-intro" class="large-text" rows="2" name="<?php echo esc_attr($o); ?>[video_intro]" placeholder="<?php esc_attr_e('See this product in action…', 'plogins-reel'); ?>"><?php echo esc_textarea((string) $s['video_intro']); ?></textarea>
                                </div>
                            </div>

                        </div>
                    </section>

                    <section class="reel-admin__card">
                        <div class="reel-admin__card-head">
                            <span class="dashicons dashicons-shortcode" aria-hidden="true"></span>
                            <div>
                                <h2><?php esc_html_e('Place it anywhere', 'plogins-reel'); ?></h2>
                                <p><?php esc_html_e('Beyond the automatic gallery position, drop the current product\'s video wherever you like.', 'plogins-reel'); ?></p>
                            </div>
                        </div>
                        <div class="reel-admin__card-body">
                            <div class="reel-field">
                                <span class="reel-field__label"><?php esc_html_e('Shortcode', 'plogins-reel'); ?></span>
                                <div class="reel-field__control">
                                    <code class="reel-admin__code">[reel_video]</code>
                                </div>
                                <p class="reel-field__hint"><?php esc_html_e('Paste into any product description or page. Optional attributes: id="123" to target a specific product, and title="hide" to drop the heading.', 'plogins-reel'); ?></p>
                            </div>
                            <div class="reel-field">
                                <span class="reel-field__label"><?php esc_html_e('Block', 'plogins-reel'); ?></span>
                                <div class="reel-field__control">
                                    <code class="reel-admin__code"><?php esc_html_e('Reel: Featured video', 'plogins-reel'); ?></code>
                                </div>
                                <p class="reel-field__hint"><?php esc_html_e('Search for it in the block inserter. Renders the same video as the shortcode.', 'plogins-reel'); ?></p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="reel-admin__submit">
                    <?php submit_button(__('Save changes', 'plogins-reel')); ?>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Sanitises and clamps the submitted settings before save.
     *
     * @param mixed $raw
     * @return array{enable_zoom:bool,enable_lightbox:bool,zoom_scale:float,show_backdrop_close:bool,disable_zoom_on_touch:bool,lightbox_caption:bool,trigger_label:string,enable_video:bool,video_position:string,video_autoplay:bool,video_show_title:bool,video_title:string,video_intro:string}
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        $scale = isset($raw['zoom_scale']) ? (float) $raw['zoom_scale'] : 1.45;
        $scale = max(1.0, min(3.0, $scale));

        $position = isset($raw['video_position']) ? (string) $raw['video_position'] : 'after_gallery';
        if (! in_array($position, ['after_gallery', 'before_summary'], true)) {
            $position = 'after_gallery';
        }

        return [
            'enable_zoom'           => ! empty($raw['enable_zoom']),
            'enable_lightbox'       => ! empty($raw['enable_lightbox']),
            'zoom_scale'            => $scale,
            'show_backdrop_close'   => ! empty($raw['show_backdrop_close']),
            'disable_zoom_on_touch' => ! empty($raw['disable_zoom_on_touch']),
            'lightbox_caption'      => ! empty($raw['lightbox_caption']),
            'trigger_label'         => isset($raw['trigger_label']) ? sanitize_text_field((string) $raw['trigger_label']) : '',
            'enable_video'          => ! empty($raw['enable_video']),
            'video_position'        => $position,
            'video_autoplay'        => ! empty($raw['video_autoplay']),
            'video_show_title'      => ! empty($raw['video_show_title']),
            'video_title'           => isset($raw['video_title']) ? sanitize_text_field((string) $raw['video_title']) : '',
            'video_intro'           => isset($raw['video_intro']) ? sanitize_textarea_field((string) $raw['video_intro']) : '',
        ];
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
}
