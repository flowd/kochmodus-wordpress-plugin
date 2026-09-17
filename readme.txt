=== Flowd – Cooking Mode ===
Contributors: kochmodus
Tags: recipe, cooking, widget, gutenberg, shortcode
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.0.2
Requires PHP: 7.4
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
* Translation-ready (Text Domain: `flowd-kochmodus`)

**Requirements**

* A Kochmodus account and Access Token from [kochmodus.de](https://kochmodus.de)
* WordPress 6.0 or newer
* PHP 7.4 or newer
* Recipe pages with valid [schema.org/Recipe](https://schema.org/Recipe) JSON-LD data

**Source code**

The complete, unminified source code — including the Gutenberg block sources — ships with the plugin (`blocks/kochmodus-button/src`) and is also available at [github.com/flowd/kochmodus-wordpress-plugin](https://github.com/flowd/kochmodus-wordpress-plugin).

== External services ==

This plugin embeds the Kochmodus cooking mode widget, a service operated by Flowd GmbH. The widget is required for the core functionality of this plugin: it renders the gesture-controlled cooking mode when a visitor clicks a Kochmodus button.

On pages that render a Kochmodus button, the widget script is loaded from `https://app.kochmodus.de` in the visitor's browser. As with any HTTP request, the visitor's IP address and user agent are transmitted to the Kochmodus servers at that point.

When a visitor starts the cooking mode, the following data is sent to the Kochmodus service:

* The site's Access Token, which is exchanged for a short-lived session token.
* The URL of the recipe page (or the recipe URI configured on the button). Kochmodus servers fetch this URL once to read the page's public schema.org/Recipe data (cached for 15 minutes).
* Anonymous usage statistics for the cooking-mode session (e.g. steps viewed, time per step, whether gesture control was used, optional rating). No IP addresses or user agents are stored with these statistics, and no cookies are set.

The hands-free gesture control uses the visitor's camera **only after the visitor grants the browser's camera permission**. All gesture recognition runs locally in the visitor's browser; no video or audio is ever recorded or transmitted.

No data is sent to Kochmodus from pages that do not contain a Kochmodus button.

* Service provider: Flowd GmbH ([imprint](https://kochmodus.de/impressum))
* Privacy policy: [kochmodus.de/datenschutz](https://kochmodus.de/datenschutz)

== Installation ==

1. Upload the `flowd-kochmodus` folder to `/wp-content/plugins/`, or install via **Plugins > Add New > Upload Plugin**.
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

= The cooking mode opens but shows no recipe. Why? =

The recipe page needs valid [schema.org/Recipe](https://schema.org/Recipe) JSON-LD data. Verify your page with the [Google Rich Results Test](https://search.google.com/test/rich-results).

= The button renders but does not react to clicks. Why? =

Check the Access Token under **Settings > Kochmodus** and make sure your WordPress domain is registered under **Domains** in the Kochmodus dashboard.

= Does the widget script load on every page? =

No. It is only enqueued on pages that actually render a Kochmodus button.

= Can I change the button label or colors for the whole site at once? =

Yes. Under **Settings > Kochmodus** you can set a default label and default colors (background, hover background, label) for all buttons. Values set on an individual post or page always take precedence.

== Screenshots ==

1. The Kochmodus Button block with its sidebar settings in the Gutenberg editor.
2. The Kochmodus settings page under **Settings > Kochmodus**.

== Changelog ==

= 1.0.2 =
* Added animated plugin banners for the WordPress.org listing. No functional changes.

= 1.0.1 =
* Updated the block build toolchain (@wordpress/scripts 35) and rebuilt the Gutenberg block.

= 1.0.0 =
* Initial release.
* Gutenberg block **Kochmodus Button** with label, recipe URI and color controls.
* `[kochmodus_button]` shortcode with equivalent attributes.
* Settings page for the Access Token.
* Conditional widget script loading in `wp_footer`.

== Upgrade Notice ==

= 1.0.2 =
Maintenance release: new WordPress.org listing banners. No functional changes.

= 1.0.1 =
Maintenance release: updated block build toolchain. No functional changes.

= 1.0.0 =
Initial release of the Kochmodus plugin.
