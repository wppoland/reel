<?php
/**
 * Featured product video, rendered on the single product page. The embed/video
 * HTML is built via WordPress (wp_video_shortcode / wp_oembed_get) and is
 * already safe markup.
 *
 * @package Reel
 *
 * @var string         $video_html Embed or <video> markup.
 * @var string         $title      Heading text.
 * @var string         $intro_text Optional intro copy.
 * @var bool           $show_title Whether to render the heading.
 * @var bool           $show_intro Whether to render the intro.
 * @var \WC_Product    $product
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (! isset($video_html) || $video_html === '') {
    return;
}
?>
<div class="reel-featured-video">
    <?php if (! empty($show_title) && isset($title) && $title !== '') : ?>
        <p class="reel-featured-video__eyebrow"><?php esc_html_e('Now playing', 'reel'); ?></p>
        <h2 class="reel-featured-video__title"><?php echo esc_html((string) $title); ?></h2>
    <?php endif; ?>

    <?php if (! empty($show_intro) && isset($intro_text) && $intro_text !== '') : ?>
        <p class="reel-featured-video__intro"><?php echo esc_html((string) $intro_text); ?></p>
    <?php endif; ?>

    <div class="reel-featured-video__embed">
        <?php
        // Engine-built embed/video markup (wp_video_shortcode / wp_oembed_get).
        echo wp_kses_post((string) $video_html);
        ?>
    </div>
</div>
