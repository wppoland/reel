<?php
/**
 * Gallery lightbox shell, printed in the footer by the storefront-kit
 * GalleryZoomEngine. Markup matches the data attributes the gallery-zoom.js
 * script binds to. Starts hidden (no CLS — fixed overlay, zero layout cost).
 *
 * @package Reel
 *
 * @var array<string, mixed> $settings
 */

declare(strict_types=1);

defined('ABSPATH') || exit;
?>
<div class="reel-gallery-lightbox" data-reel-gallery-lightbox hidden role="dialog" aria-modal="true" tabindex="-1">
    <button
        type="button"
        class="reel-gallery-lightbox__close"
        data-reel-gallery-lightbox-close
        aria-label="<?php esc_attr_e('Close', 'reel'); ?>"
    >&times;</button>
    <img data-reel-gallery-lightbox-image src="" alt="" />
    <?php if (! empty($settings['lightbox_caption'])) : ?>
        <p class="reel-gallery-lightbox__caption" data-reel-gallery-lightbox-caption></p>
    <?php endif; ?>
</div>
