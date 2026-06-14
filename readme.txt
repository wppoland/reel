=== Reel - Gallery and Video for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, product gallery, image zoom, lightbox, product video
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hover zoom, an accessible lightbox and a featured product video for the WooCommerce product gallery — reserved space, no jQuery.

== Description ==

Reel upgrades the WooCommerce single product gallery with three things shoppers
expect from a modern store:

* **Hover zoom** — gallery images magnify smoothly on hover, with a configurable
  zoom scale. The transform is clipped to the gallery frame, so nothing shifts.
* **Accessible lightbox** — click (or press Enter/Space) any gallery image to open
  it full screen. The lightbox is fully keyboard operable, traps focus on its
  close control, restores focus on close, and dismisses on Escape or a backdrop
  click. It is a fixed overlay that starts hidden, so it costs zero layout shift.
* **Featured video** — show a per-product video (self-hosted MP4/WebM or an oEmbed
  URL such as YouTube or Vimeo) either after the gallery or before the product
  summary, inside a responsive 16:9 frame that reserves its space (no CLS).

Everything is rendered server-side and enhanced with a single small vanilla-JS
file — **no jQuery**, deferred and loaded in the footer. Assets only load on the
single product page.

Configuration lives under a top-level **Reel** admin menu: toggle hover zoom, the
lightbox and the featured video independently, set the zoom scale and video
position, choose autoplay, show an alt-text caption in the lightbox, skip hover
zoom on touch devices, and set a default video heading and intro. The per-product
video URL is read from the `_reel_video_url` product meta field (with an optional
`_reel_video_title`).

Need the video somewhere other than the gallery area? Drop the `[reel_video]`
shortcode (or the **Reel: Featured video** block) into any product content and it
renders the current product's video, with the same reserved-space, no-CLS frame.

The media engines are provided by the shared **storefront-kit** package, so the
same battle-tested code powers Reel and its sibling WPPoland plugins.

= Features =

* Gallery image hover zoom with a configurable scale.
* Accessible, keyboard-operable full-screen lightbox (Escape / backdrop close).
* Featured product video (self-hosted or oEmbed) with selectable position.
* `[reel_video]` shortcode and a "Reel: Featured video" block to place the video anywhere.
* Optional lightbox caption from the image alt text.
* Skip hover zoom on touch devices (where hover is unreliable).
* Custom accessible label for the open-in-lightbox control.
* Default video heading and optional intro paragraph.
* Reserved-space markup throughout — no Cumulative Layout Shift.
* No jQuery; a single deferred, in-footer script loaded only on product pages.
* Global on/off toggles per feature.
* "Settings" link on the plugins list; clean uninstall removes plugin options.
* Translation-ready (bundled .pot).
* HPOS and cart/checkout blocks compatible.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/reel`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Go to the **Reel** menu and enable the features you want.
4. For a product video, set the video URL in the product's `_reel_video_url` meta.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes.

= Which video sources are supported? =

Self-hosted files (MP4, M4V, WebM, OGV) are played with WordPress's native video
player. Any oEmbed-supported URL (YouTube, Vimeo, etc.) is embedded automatically.

= Does it use jQuery? =

No. Reel ships a single small vanilla-JavaScript file, deferred and loaded in the
footer, only on the single product page.

= Will it cause layout shift (CLS)? =

No. The lightbox is a fixed overlay that starts hidden, the zoom transform is
clipped to the gallery frame, and the video sits in a fixed-ratio frame that
reserves its space before loading.

== Screenshots ==

1. Gallery hover zoom on a single product page.
2. The accessible full-screen lightbox.
3. A featured product video below the gallery.
4. The Reel settings screen.

== Changelog ==

= 0.2.0 =
* Add `[reel_video]` shortcode and a "Reel: Featured video" block to place the featured video anywhere.
* Add lightbox caption (from image alt text) and an option to skip hover zoom on touch devices.
* Add settings for the open-in-lightbox label, a default video heading and an optional intro paragraph.
* Add a "Settings" link on the plugins list and an uninstall routine that removes plugin options.
* Bundle a translations template (languages/reel.pot) and load the text domain.

= 0.1.0 =
* Initial release: gallery hover zoom, accessible lightbox and featured product video.
