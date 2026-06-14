# Reel - Gallery and Video for WooCommerce

Reel upgrades the WooCommerce single product gallery with hover zoom, an accessible lightbox and a featured product video — everything rendered with reserved space so nothing shifts on the page.

## Features

- Hover zoom on gallery images with a configurable scale, clipped to the gallery frame.
- Accessible, keyboard-operable full-screen lightbox (Escape or backdrop to close, focus restored on close).
- Featured product video (self-hosted MP4/WebM or an oEmbed URL such as YouTube or Vimeo) in a responsive 16:9 frame.
- `[reel_video]` shortcode and a "Reel: Featured video" block to place the video anywhere.
- Per-feature on/off toggles, optional lightbox caption from alt text, and a touch-device hover-zoom skip.
- No jQuery: a single deferred, in-footer script loaded only on product pages. HPOS and cart/checkout blocks compatible.

## Installation

1. Upload the plugin to `/wp-content/plugins/reel`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Go to the **Reel** menu and enable the features you want.
4. For a product video, set the video URL in the product's `_reel_video_url` meta.

## Frequently Asked Questions

**Which video sources are supported?**
Self-hosted files (MP4, M4V, WebM, OGV) play with WordPress's native video player. Any oEmbed-supported URL (YouTube, Vimeo, etc.) is embedded automatically.

**Will it cause layout shift?**
No. The lightbox is a fixed overlay that starts hidden, the zoom transform is clipped to the gallery frame, and the video sits in a fixed-ratio frame that reserves its space.

---

Built by WPPoland — https://plogins.com

License: GPL-2.0-or-later
