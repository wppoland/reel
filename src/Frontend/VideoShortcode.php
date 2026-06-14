<?php

declare(strict_types=1);

namespace Reel\Frontend;

defined('ABSPATH') || exit;

use Reel\Contract\HasHooks;
use Reel\Service\ReelService;

/**
 * Registers the `[reel_video]` shortcode and a matching dynamic block
 * (`reel/featured-video`) so a merchant can place the featured product video
 * anywhere in the content, not only at the gallery position.
 *
 * Both render the SAME engine-built markup the gallery position uses (so this is
 * a placement option for the FREE featured video, not a second feature). The
 * video URL/title still come from the existing product meta — no new editor UI
 * here (per-product video URL admin field is reserved for Reel Pro).
 */
final class VideoShortcode implements HasHooks
{
    public function __construct(private readonly ReelService $service)
    {
    }

    public function registerHooks(): void
    {
        add_shortcode('reel_video', [$this, 'renderShortcode']);

        // Server-rendered block. No build step: a render_callback block whose
        // markup matches the shortcode output.
        add_action('init', [$this, 'registerBlock']);
    }

    /**
     * `[reel_video]` — render the featured video.
     *
     * @param array<string, string>|string $atts Shortcode attributes:
     *        `id` (product ID; defaults to the current product) and
     *        `title` ("show"/"hide" the heading; defaults to the setting).
     */
    public function renderShortcode(array|string $atts): string
    {
        $atts = shortcode_atts(
            ['id' => '0', 'title' => ''],
            is_array($atts) ? $atts : [],
            'reel_video',
        );

        $showTitle = null;
        if ($atts['title'] === 'hide') {
            $showTitle = false;
        } elseif ($atts['title'] === 'show') {
            $showTitle = true;
        }

        return $this->render((int) $atts['id'], $showTitle);
    }

    public function registerBlock(): void
    {
        if (! function_exists('register_block_type')) {
            return;
        }

        register_block_type('reel/featured-video', [
            'api_version'     => '3',
            'title'           => __('Reel: Featured video', 'reel'),
            'category'        => 'media',
            'icon'            => 'format-video',
            'description'     => __('Show the current product\'s featured video (Reel).', 'reel'),
            'attributes'      => [
                'productId' => ['type' => 'number', 'default' => 0],
                'showTitle' => ['type' => 'boolean', 'default' => true],
            ],
            'render_callback' => [$this, 'renderBlockCallback'],
            'supports'        => ['html' => false],
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function renderBlockCallback(array $attributes): string
    {
        $productId = isset($attributes['productId']) ? (int) $attributes['productId'] : 0;
        $showTitle = isset($attributes['showTitle']) ? (bool) $attributes['showTitle'] : null;

        return $this->render($productId, $showTitle);
    }

    /**
     * Resolve the product and build the wrapped video markup.
     */
    private function render(int $productId, ?bool $showTitle): string
    {
        $product = $this->resolveProduct($productId);

        if (! $product instanceof \WC_Product) {
            return '';
        }

        $videoHtml = $this->service->renderVideoHtml($product);

        if ($videoHtml === '') {
            return '';
        }

        $title = $this->service->videoTitleFor($product);

        // The engine only enqueues the video CSS on single product pages; the
        // shortcode/block can appear anywhere, so ensure the style is present.
        if (! wp_style_is('reel-featured-video', 'enqueued')) {
            wp_enqueue_style(
                'reel-featured-video',
                REEL_URL . 'assets/css/featured-video.css',
                [],
                \Reel\VERSION,
            );
        }

        ob_start();
        ?>
        <div class="reel-featured-video reel-featured-video--shortcode">
            <?php if ($showTitle !== false && $title !== '') : ?>
                <h2 class="reel-featured-video__title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            <div class="reel-featured-video__embed">
                <?php echo wp_kses_post($videoHtml); ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function resolveProduct(int $productId): ?\WC_Product
    {
        if ($productId <= 0) {
            $productId = function_exists('get_the_ID') ? (int) get_the_ID() : 0;
        }

        if ($productId <= 0 || ! function_exists('wc_get_product')) {
            return null;
        }

        $product = wc_get_product($productId);

        return $product instanceof \WC_Product ? $product : null;
    }
}
