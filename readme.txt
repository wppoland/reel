=== Reel - Gallery and Video for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, product gallery, product video, image zoom, gallery slider
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce product gallery upgrades: image zoom, gallery lightbox, gallery slider controls and product video. No jQuery.

== Description ==

Reel upgrades the WooCommerce single product gallery with product image zoom, an accessible gallery lightbox and a featured product video:

* **Hover zoom.** Gallery images magnify on hover at a zoom scale you set (1.0× to
  3.0×). The transform is clipped to the gallery frame, so the rest of the page
  stays put.
* **Accessible lightbox.** Click, or press Enter/Space, on any gallery image to
  open it full screen. The lightbox is keyboard-operable: Tab stays on the close
  button so focus can't slip behind the overlay, Escape closes it, and focus
  returns to the image you opened. It's a fixed overlay that starts hidden, so it
  reserves no space until used.
* **Featured video.** Show a per-product video — a self-hosted MP4/WebM file or a
  YouTube/Vimeo (oEmbed) URL — after the gallery or before the product summary.
  The video sits in a 16:9 frame sized with `aspect-ratio`, so its space is held
  before it loads.

The markup is built in PHP and progressively enhanced by one vanilla-JavaScript
file (no jQuery), deferred and loaded in the footer. Scripts and styles only
enqueue on the single product page.

Settings live under a top-level **Reel** admin menu. Each of the three features
has its own on/off switch; you can also set the zoom scale and skip it on touch
devices, show an alt-text caption in the lightbox, relabel the open-image control
for screen readers, and choose the video's position, autoplay, heading and intro
text. The per-product video URL comes from the `_reel_video_url` product meta
field, with an optional `_reel_video_title` for that product's heading.

To place the video somewhere other than the gallery area, drop the `[reel_video]`
shortcode (it takes `id` and `title` attributes) or the **Reel: Featured video**
block into any product content. Both render the current product's video in the
same 16:9 frame.

Source and issue tracker: https://github.com/wppoland/reel — the plugin is
developed in the open, so bug reports and pull requests are welcome there.

= Features =

* Gallery image hover zoom with a configurable scale.
* Accessible, keyboard-operable full-screen lightbox (Escape / backdrop close).
* Featured product video (self-hosted or oEmbed) with selectable position.
* `[reel_video]` shortcode and a "Reel: Featured video" block to place the video anywhere.
* Optional lightbox caption from the image alt text.
* Skip hover zoom on touch devices (where hover is unreliable).
* Custom accessible label for the open-in-lightbox control.
* Default video heading and optional intro paragraph.
* Reserved-space markup throughout, so no Cumulative Layout Shift.
* No jQuery; one deferred, in-footer script loaded only on product pages.
* Independent on/off toggle for each feature.
* "Settings" link on the plugins list; clean uninstall removes plugin options.
* Translation-ready: bundled .pot template plus a Polish (pl_PL) translation.
* HPOS and cart/checkout blocks compatible.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/reel`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Go to the **Reel** menu and enable the features you want.
4. For a product video, set the video URL in the product's `_reel_video_url` meta.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. Reel is a WooCommerce product gallery plugin and runs on single product pages.

= Which video sources are supported? =

Self-hosted files (MP4, M4V, WebM, OGV) are played with WordPress's native video
player. Any oEmbed-supported URL (YouTube, Vimeo, etc.) is embedded automatically.

= Does it use jQuery? =

No. Reel ships one vanilla-JavaScript file, deferred and loaded in the footer,
and only on the single product page.

= Does Reel replace the WooCommerce product gallery? =

No. Reel enhances the existing WooCommerce product gallery with image zoom, lightbox behaviour and optional product video.

= Can I show a product video outside the gallery? =

Yes. Use the `[reel_video]` shortcode or the "Reel: Featured video" block to place the product video in custom product content.

= Will it cause layout shift (CLS)? =

No. The lightbox is a fixed overlay that starts hidden, the zoom transform is
clipped to the gallery frame, and the video sits in a fixed-ratio frame that
reserves its space before loading.

= Is the lightbox keyboard accessible? =

Yes. Shoppers can open images with Enter or Space, close with Escape, and focus returns to the image that opened the lightbox.

== Screenshots ==

1. Gallery hover zoom on a single product page.
2. The accessible full-screen lightbox.
3. A featured product video below the gallery.
4. The Reel settings screen.

== Changelog ==

= 0.2.0 =
* Redesigned settings screen: card layout, toggle switches, inline help tooltips and a live zoom-strength control.
* Polished storefront styling: themeable CSS custom properties, fluid sizing, dark-mode support and reduced-motion guards.
* Accessibility: named lightbox dialog, role=tooltip help, visible focus styles and full keyboard operability.
* Robustness: graceful empty/placeholder states, a no-layout-shift video skeleton and hardened event handling.
* Add `[reel_video]` shortcode and a "Reel: Featured video" block to place the featured video anywhere.
* Add lightbox caption (from image alt text) and an option to skip hover zoom on touch devices.
* Add settings for the open-in-lightbox label, a default video heading and an optional intro paragraph.
* Add a "Settings" link on the plugins list and an uninstall routine that removes plugin options.
* Bundle a translation template (languages/reel.pot) and a Polish translation.

= 0.1.0 =
* Initial release: gallery hover zoom, accessible lightbox and featured product video.
