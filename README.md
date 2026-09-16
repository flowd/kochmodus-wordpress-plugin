# Flowd – Cooking Mode (WordPress Plugin)

WordPress plugin to embed the [Kochmodus](https://kochmodus.de) cooking mode widget on recipe pages. Allows visitors to enter a gesture-controlled cooking mode for any recipe on your site.

## Requirements

- WordPress >= 6.0
- PHP >= 7.4
- A Kochmodus account with an Access Token — sign up at [kochmodus.de](https://kochmodus.de)
- Recipe pages must contain valid [schema.org/Recipe JSON-LD data](https://schema.org/Recipe), otherwise the cooking mode cannot load the recipe

## Installation

### Option 1: Install via Composer

For WordPress projects managed with Composer, require the plugin:

```bash
composer require flowd/kochmodus-wordpress-plugin
```

Then activate the plugin in the WordPress admin under **Plugins**.

### Option 2: Upload ZIP via WordPress Admin

1. Download the latest `flowd-kochmodus.zip` from the [GitHub Releases](https://github.com/flowdgmbh/kochmodus-wordpress-plugin/releases) page (attached as a release artifact)
2. In the WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Select the ZIP file and click **Install Now**
4. Activate the plugin

### Option 3: WordPress Plugin Directory

1. In the WordPress admin, go to **Plugins > Add New**
2. Search for **Flowd – Cooking Mode**
3. Click **Install Now**, then **Activate**

## Configuration

### Access Token

Go to **Settings > Kochmodus** in the WordPress admin and enter your Access Token. You find the token in your [Kochmodus dashboard](https://kochmodus.de/dashboard/token) under **Token**.

### Button Defaults

On the same settings page you can optionally set global defaults for all Kochmodus buttons:

- **Button Label**
- **Background Color**
- **Hover Background Color**
- **Label Color**

The color fields use the native WordPress color picker; hex (`#rrggbb`) and `rgb()`/`rgba()` values are accepted. Leave a field empty to use the widget's built-in default. Values set on an individual post or page (via block sidebar or shortcode attributes) always take precedence over these defaults.

The default colors are also printed as CSS custom properties on `:root` in `wp_head`, so they even apply to `<kochmodus-button>` elements hand-coded in theme templates. Note that for hand-coded buttons the default *label* does not apply (it is an HTML attribute, not CSS), and you have to set the `data-kochmodus-access-token` attribute and load the widget script yourself.

### Widget Script URL (Development Override)

By default, the plugin loads the widget script from `https://app.kochmodus.de/build/assets/kochmodus-widget.js`.

For local development, you can override this by adding the following to your `wp-config.php`:

```php
define('KOCHMODUS_WIDGET_SCRIPT_URL', 'https://app.kochmodus.localdev/build/assets/kochmodus-widget.js');
```

## Usage

### Gutenberg Block

Search for **Kochmodus Button** in the block inserter. The block provides settings in the sidebar for:

- **Button Label** - defaults to "Kochmodus starten"
- **Recipe URI** - leave empty to use the current page URL
- **Background Color** - custom button background color
- **Hover Background Color** - custom button hover background color
- **Text Color** - custom button text color

### Shortcode

```
[kochmodus_button]
```

With all available attributes:

```
[kochmodus_button label="Start Cooking" recipe_uri="https://example.com/recipe/" background_color="#ff0000" hover_background_color="#cc0000" color="#ffffff"]
```

All attributes are optional. When no `recipe_uri` is provided, the widget falls back to the current browser URL.

### Output

The plugin renders a `<kochmodus-button>` web component:

```html
<kochmodus-button
    label="Kochmodus starten"
    data-kochmodus-access-token="YOUR_TOKEN"
></kochmodus-button>
```

With colors and recipe URI set:

```html
<kochmodus-button
    label="Kochmodus starten"
    data-kochmodus-recipe-uri="https://example.com/recipe/"
    style="--kochmodus-button-background: #ff0000; --kochmodus-button-hover-background: #cc0000; --kochmodus-button-color: #ffffff"
    data-kochmodus-access-token="YOUR_TOKEN"
></kochmodus-button>
```

The widget script (`<script type="module">`) is only loaded on pages where the button is actually used.

## Troubleshooting

**The button renders but does not react to clicks.**
Check the Access Token under **Settings > Kochmodus** and make sure your WordPress domain is registered under **Domains** in the Kochmodus dashboard.

**The cooking mode opens but shows no recipe.**
The recipe page needs valid [schema.org/Recipe JSON-LD data](https://schema.org/Recipe). Verify with the [Google Rich Results Test](https://search.google.com/test/rich-results).

**The styling does not match the theme.**
The button colors can also be overridden globally via theme CSS using the custom properties `--kochmodus-button-background`, `--kochmodus-button-hover-background` and `--kochmodus-button-color`.

**The widget script is not loaded.**
The script is only enqueued on pages that render the Kochmodus block or shortcode. Check that the block was actually added to the content.

## Development

```bash
composer install          # Install PHP dependencies
composer test             # Run unit tests
composer analyse          # Run PHPStan (level 9)
composer cs:fix           # Run PHP CS Fixer

npm install               # Install JS dependencies
npm run build             # Build Gutenberg block
npm run start             # Watch mode for block development
```

### Architecture

DDD layered architecture with PSR-4 autoloading (namespace: `Flowd\KochmodusWordpressPlugin\`):

- `src/Domain/` - Pure PHP Value Objects, zero WordPress dependencies
- `src/Application/` - Services (SettingsService, RenderButtonService)
- `src/Infrastructure/` - WordPress integration (hooks, shortcodes, blocks, persistence)

### Testing

PHPUnit 9.x with [Brain\Monkey](https://github.com/Brain-WP/BrainMonkey) for WordPress function mocking. Domain tests are pure PHP, infrastructure tests extend `WordPressTestCase`.

### Commit Messages

Commits follow the [Conventional Commits](https://www.conventionalcommits.org/) format defined in [`.gitmessage`](.gitmessage). Enable the template once per clone:

```bash
git config commit.template .gitmessage
```

### Releasing

Git is the development repository; the [WordPress.org SVN repository](https://plugins.svn.wordpress.org/flowd-kochmodus/) only receives releases. The [Release workflow](.github/workflows/release.yml) deploys automatically when a GitHub release is published:

1. Bump the version (following [Semantic Versioning](https://semver.org/)) in `flowd-kochmodus.php` (header and `KOCHMODUS_VERSION`), `readme.txt` (`Stable tag`), `package.json` and `blocks/kochmodus-button/block.json`, and add a `= X.Y.Z =` entry to the changelog in `readme.txt`. `bash .github/scripts/check-version.sh` verifies that all of them agree.
2. Merge to `main` and publish a GitHub release with the tag `vX.Y.Z` (pre-releases are not deployed).
3. The workflow runs the full CI, builds the plugin and deploys it to SVN as described in the [WordPress.org SVN guide](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/): trunk is updated, copied to `tags/X.Y.Z`, and the wp.org `assets/` are synced. The installable zip is attached to the GitHub release.

The SVN commit messages are defined in [`.github/scripts/svn-deploy.sh`](.github/scripts/svn-deploy.sh) and are independent of the Conventional Commits used in git. Running the workflow manually (*Run workflow*) performs a dry run: everything is built and validated and the SVN changes are shown, but nothing is committed. The same dry run works locally with `svn` installed:

```bash
SLUG=flowd-kochmodus VERSION=1.0.0 DRY_RUN=1 bash .github/scripts/svn-deploy.sh
```

Required repository secrets: `SVN_USERNAME` (WordPress.org username) and `SVN_PASSWORD` (the WordPress.org [SVN password](https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password), not the account password).

## License

GPL-2.0-or-later
