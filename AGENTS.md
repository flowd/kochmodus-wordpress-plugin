# Kochmodus WordPress Plugin

WordPress plugin to embed the Kochmodus cooking mode widget on recipe pages.

## Requirements
- WordPress >= 6.0
- PHP >= 7.4

## Development Commands
```bash
composer install          # Install PHP dependencies
composer test             # Run all tests
composer test:unit        # Run unit tests only
npm install               # Install JS dependencies (for Gutenberg block)
npm run build             # Build Gutenberg block
npm run start             # Watch mode for block development
```

## Architecture
DDD layered architecture with PSR-4 autoloading (namespace: `Flowd\KochmodusWordpressPlugin\`):
- `src/Domain/` — Pure PHP Value Objects and Entities, zero WordPress dependencies
- `src/Application/` — Services (SettingsService, RenderButtonService)
- `src/Infrastructure/` — WordPress integration (hooks, shortcodes, blocks, persistence)

## Testing
- PHPUnit 9.x with Brain\Monkey for WordPress function mocking
- Domain tests: pure PHP, no mocking needed
- Infrastructure tests: extend `WordPressTestCase` (Brain\Monkey setup)
- TDD: write tests first, then implement

## Key Patterns
- Settings stored in single `wp_options` row (key: `kochmodus_settings`)
- Button rendering shared between Shortcode and Gutenberg Block via `RenderButtonService`
- Widget script conditionally loaded via flag-based `ScriptEnqueuer` in `wp_footer`
- HTML escaping injected as callable to keep Domain pure
