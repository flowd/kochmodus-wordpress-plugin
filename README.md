# Kochmodus WordPress Plugin

WordPress plugin to embed the [Kochmodus](https://kochmodus.de) cooking mode widget on recipe pages. Allows visitors to enter a gesture-controlled cooking mode for any recipe on your site.

## Requirements

- WordPress >= 6.0
- PHP >= 7.4

## Installation

### Option 1: Install via Composer

For WordPress projects managed with Composer, require the plugin:

```bash
composer require flowd/kochmodus-wordpress-plugin
```

Then activate the plugin in the WordPress admin under **Plugins**.

### Option 2: Upload ZIP via WordPress Admin

1. Download the latest `kochmodus.zip` from the [GitHub Releases](https://github.com/flowdgmbh/kochmodus-wordpress-plugin/releases) page (attached as a release artifact)
2. In the WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Select the ZIP file and click **Install Now**
4. Activate the plugin

### Option 3: WordPress Plugin Directory

1. In the WordPress admin, go to **Plugins > Add New**
2. Search for **Kochmodus**
3. Click **Install Now**, then **Activate**

## Configuration

### Access Token

Go to **Settings > Kochmodus** in the WordPress admin and enter your Access Token.

### Widget Script URL (Development Override)

By default, the plugin loads the widget script from `https://kochmodus.de/build/assets/kochmodus-widget.js`.

For local development, you can override this by adding the following to your `wp-config.php`:

```php
define('KOCHMODUS_WIDGET_SCRIPT_URL', 'https://kochmodus-app.ddev.site/build/assets/kochmodus-widget.js');
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

DDD layered architecture with PSR-4 autoloading (namespace: `Kochmodus\`):

- `src/Domain/` - Pure PHP Value Objects, zero WordPress dependencies
- `src/Application/` - Services (SettingsService, RenderButtonService)
- `src/Infrastructure/` - WordPress integration (hooks, shortcodes, blocks, persistence)

### Testing

PHPUnit 9.x with [Brain\Monkey](https://github.com/Brain-WP/BrainMonkey) for WordPress function mocking. Domain tests are pure PHP, infrastructure tests extend `WordPressTestCase`.

## License

GPL-2.0-or-later
