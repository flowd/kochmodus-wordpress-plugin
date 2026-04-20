=== Kochmodus ===
Contributors: flowdgmbh
Tags: recipe, cooking, widget, gutenberg, shortcode
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: ^7.4|^8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embeds the Kochmodus cooking mode widget on recipe pages, letting visitors follow recipes hands-free via a gesture-controlled cooking mode.

== Description ==

Kochmodus adds a button to your recipe pages that launches a gesture-controlled cooking mode. Visitors can step through recipes without touching their screen — ideal for cooking with messy hands.

The plugin loads the widget script only on pages where the button is actually used.

**Features**

* Gutenberg block **Kochmodus Button** with sidebar controls for label, recipe URI and colors
* `[kochmodus_button]` shortcode for classic editor and page builders
* Custom button colors (background, hover background, text color)
* Per-button recipe URI override (defaults to the current page URL)
* Conditional script loading — widget JS is only enqueued when a button is rendered
* Translation-ready (Text Domain: `kochmodus`)

**Requirements**

* A Kochmodus account and Access Token from [kochmodus.de](https://kochmodus.de)
* WordPress 6.0 or newer
* PHP 7.4 or newer

== Installation ==

1. Upload the `kochmodus` folder to `/wp-content/plugins/`, or install via **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > Kochmodus** and enter your Access Token.
4. Add the **Kochmodus Button** block to a recipe page, or use the `[kochmodus_button]` shortcode.

== Frequently Asked Questions ==

= Where do I get an Access Token? =

Sign up at [kochmodus.de](https://kochmodus.de) and copy the Access Token from your account settings. Enter it under **Settings > Kochmodus** in WordPress.

= Can I use the button without Gutenberg? =

Yes. Use the `[kochmodus_button]` shortcode in the classic editor or any page builder that supports shortcodes.

= Which attributes does the shortcode support? =

`label`, `recipe_uri`, `background_color`, `hover_background_color` and `color`. All attributes are optional. Example:

`[kochmodus_button label="Start Cooking" recipe_uri="https://example.com/recipe/" background_color="#ff0000" hover_background_color="#cc0000" color="#ffffff"]`

= What happens if I leave the recipe URI empty? =

The widget falls back to the current browser URL.

= Does the widget script load on every page? =

No. It is only enqueued on pages that actually render a Kochmodus button.

== Screenshots ==

1. The Kochmodus Button block with its sidebar settings in the Gutenberg editor.
2. The Kochmodus settings page under **Settings > Kochmodus**.

== Changelog ==

= 1.0.0 =
* Initial release.
* Gutenberg block **Kochmodus Button** with label, recipe URI and color controls.
* `[kochmodus_button]` shortcode with equivalent attributes.
* Settings page for the Access Token.
* Conditional widget script loading in `wp_footer`.

== Upgrade Notice ==

= 1.0.0 =
Initial release of the Kochmodus plugin.
