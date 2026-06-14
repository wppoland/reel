/**
 * Reel — gallery hover-zoom + accessible lightbox.
 *
 * No jQuery, deferred, in-footer. Config is injected via wp_localize_script as
 * window.reelGalleryZoom. The lightbox shell is printed in the footer
 * (templates/lightbox.php) and starts hidden, so there is no layout shift.
 */
document.addEventListener('DOMContentLoaded', () => {
  const config = window.reelGalleryZoom;

  if (!config) {
    return;
  }

  // Plugin-local extras injected on the same handle (only the core config object
  // is localized otherwise). Optional — defaults keep prior behaviour.
  const extra = window.reelGalleryExtra || {};
  const isTouch = window.matchMedia ? window.matchMedia('(hover: none)').matches : false;
  const allowZoom = config.enableZoom && !(extra.disableZoomOnTouch && isTouch);

  const lightbox = document.querySelector('[data-reel-gallery-lightbox]');
  const lightboxImage = document.querySelector('[data-reel-gallery-lightbox-image]');
  const closeButton = lightbox ? lightbox.querySelector('[data-reel-gallery-lightbox-close]') : null;
  const caption = lightbox ? lightbox.querySelector('[data-reel-gallery-lightbox-caption]') : null;

  // Element focused before the lightbox opened, so we can restore it on close.
  let lastFocused = null;

  const openLightbox = (img) => {
    if (!lightbox || !lightboxImage) {
      return;
    }

    const source = img.currentSrc || img.src;
    if (!source) {
      return;
    }

    lastFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    lightboxImage.src = source;
    lightboxImage.alt = img.alt || '';
    if (caption && extra.lightboxCaption) {
      caption.textContent = img.alt || '';
      caption.hidden = !img.alt;
    }
    lightbox.hidden = false;
    document.body.classList.add('reel-gallery-lightbox-open');

    if (closeButton && typeof closeButton.focus === 'function') {
      closeButton.focus();
    } else if (typeof lightbox.focus === 'function') {
      lightbox.focus();
    }
  };

  const closeLightbox = () => {
    if (!lightbox || lightbox.hidden) {
      return;
    }

    lightbox.hidden = true;
    document.body.classList.remove('reel-gallery-lightbox-open');

    if (lastFocused && typeof lastFocused.focus === 'function' && document.contains(lastFocused)) {
      lastFocused.focus();
    }
    lastFocused = null;
  };

  document.querySelectorAll('.woocommerce-product-gallery__image img').forEach((img) => {
    if (allowZoom) {
      img.style.setProperty('--reel-gallery-zoom-scale', String(config.zoomScale || 1.45));
      img.classList.add('reel-gallery-zoomable');
    }

    if (config.enableLightbox) {
      // Make the image operable by keyboard, since a bare <img> is neither
      // focusable nor activatable with Enter/Space.
      img.classList.add('reel-gallery-lightbox-trigger');
      if (!img.hasAttribute('tabindex')) {
        img.setAttribute('tabindex', '0');
      }
      img.setAttribute('role', 'button');
      if (config.triggerLabel && !img.getAttribute('aria-label')) {
        img.setAttribute('aria-label', config.triggerLabel);
      }

      img.addEventListener('click', () => openLightbox(img));
      img.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
          event.preventDefault();
          openLightbox(img);
        }
      });
    }
  });

  document.addEventListener('click', (event) => {
    if (!lightbox || lightbox.hidden) {
      return;
    }

    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }

    const close = target.closest('[data-reel-gallery-lightbox-close]');
    const clickedLightbox = target.closest('[data-reel-gallery-lightbox]');

    if (close || (clickedLightbox === target && config.showBackdropClose !== false)) {
      closeLightbox();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (!lightbox || lightbox.hidden) {
      return;
    }

    if (event.key === 'Escape') {
      closeLightbox();
      return;
    }

    // Single-control dialog: keep Tab on the close button so focus can't
    // escape behind the modal.
    if (event.key === 'Tab' && closeButton) {
      event.preventDefault();
      closeButton.focus();
    }
  });
});
