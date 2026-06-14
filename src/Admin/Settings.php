<?php

declare(strict_types=1);

namespace Reel\Admin;

defined('ABSPATH') || exit;

use Reel\Contract\HasHooks;

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
    }

    public function addMenuPage(): void
    {
        add_menu_page(
            __('Reel Settings', 'reel'),
            __('Reel', 'reel'),
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

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $s = $this->settings();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <h2><?php esc_html_e('Gallery zoom &amp; lightbox', 'reel'); ?></h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Hover zoom', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[enable_zoom]" value="1" <?php checked((bool) $s['enable_zoom'], true); ?> />
                                    <?php esc_html_e('Zoom the gallery image on hover.', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Zoom scale', 'reel'); ?></th>
                            <td>
                                <input type="number" min="1" max="3" step="0.05" class="small-text" name="<?php echo esc_attr(self::OPTION); ?>[zoom_scale]" value="<?php echo esc_attr((string) $s['zoom_scale']); ?>" />
                                <p class="description"><?php esc_html_e('Magnification factor on hover (1.0–3.0).', 'reel'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Lightbox', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[enable_lightbox]" value="1" <?php checked((bool) $s['enable_lightbox'], true); ?> />
                                    <?php esc_html_e('Open gallery images full-screen when clicked (keyboard accessible).', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Close on backdrop', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[show_backdrop_close]" value="1" <?php checked((bool) $s['show_backdrop_close'], true); ?> />
                                    <?php esc_html_e('Close the lightbox when the dark backdrop is clicked.', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Caption', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[lightbox_caption]" value="1" <?php checked((bool) $s['lightbox_caption'], true); ?> />
                                    <?php esc_html_e('Show the image alt text as a caption inside the lightbox.', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Disable zoom on touch', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[disable_zoom_on_touch]" value="1" <?php checked((bool) $s['disable_zoom_on_touch'], true); ?> />
                                    <?php esc_html_e('Skip hover zoom on touch devices, where hover is unreliable.', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Open-image label', 'reel'); ?></th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION); ?>[trigger_label]" value="<?php echo esc_attr((string) $s['trigger_label']); ?>" />
                                <p class="description"><?php esc_html_e('Accessible label for the open-in-lightbox control. Leave empty for the default.', 'reel'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h2><?php esc_html_e('Featured video', 'reel'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Set a per-product video URL in the product meta field "_reel_video_url" (self-hosted mp4/webm or an oEmbed URL such as YouTube/Vimeo).', 'reel'); ?>
                </p>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show featured video', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[enable_video]" value="1" <?php checked((bool) $s['enable_video'], true); ?> />
                                    <?php esc_html_e('Display the product video on the single product page.', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Position', 'reel'); ?></th>
                            <td>
                                <select name="<?php echo esc_attr(self::OPTION); ?>[video_position]">
                                    <option value="after_gallery" <?php selected((string) $s['video_position'], 'after_gallery'); ?>><?php esc_html_e('After the gallery', 'reel'); ?></option>
                                    <option value="before_summary" <?php selected((string) $s['video_position'], 'before_summary'); ?>><?php esc_html_e('Before the summary', 'reel'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Autoplay', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[video_autoplay]" value="1" <?php checked((bool) $s['video_autoplay'], true); ?> />
                                    <?php esc_html_e('Start the video automatically (muted where the browser requires it).', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show title', 'reel'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[video_show_title]" value="1" <?php checked((bool) $s['video_show_title'], true); ?> />
                                    <?php esc_html_e('Show a heading above the video.', 'reel'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Default title', 'reel'); ?></th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION); ?>[video_title]" value="<?php echo esc_attr((string) $s['video_title']); ?>" />
                                <p class="description"><?php esc_html_e('Heading used when a product has no per-product video title. Leave empty for the default.', 'reel'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Intro text', 'reel'); ?></th>
                            <td>
                                <textarea class="large-text" rows="2" name="<?php echo esc_attr(self::OPTION); ?>[video_intro]"><?php echo esc_textarea((string) $s['video_intro']); ?></textarea>
                                <p class="description"><?php esc_html_e('Optional short paragraph shown under the video heading. Leave empty to hide it.', 'reel'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h2><?php esc_html_e('Placement', 'reel'); ?></h2>
                <p class="description">
                    <?php
                    $reel_placement = sprintf(
                        /* translators: %s: the [reel_video] shortcode tag. */
                        __('Place the featured video anywhere with the %s shortcode, or the "Reel: Featured video" block. Both render the current product\'s video.', 'reel'),
                        '<code>[reel_video]</code>',
                    );
                    echo wp_kses($reel_placement, ['code' => []]);
                    ?>
                </p>

                <?php submit_button(); ?>
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
