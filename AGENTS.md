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

## Releasing
- Git is the dev repo; WordPress.org SVN only receives releases via `.github/workflows/release.yml` (trigger: pushed semver tag `X.Y.Z` on `main`, creates the GitHub release itself; manual run = dry run; existing SVN tag = verify + rebuild zip only, no SVN changes)
- Version must agree in plugin header, `KOCHMODUS_VERSION`, `readme.txt` (Stable tag + changelog entry), `package.json`, `block.json` — verified by `.github/scripts/check-version.sh`
- SVN commit messages live in `.github/scripts/svn-deploy.sh`; git keeps Conventional Commits (`.gitmessage`)
- wp.org page assets (`assets/`) are not part of a release: `.github/workflows/assets-sync.yml` syncs them to SVN `/assets` on any push to `main` touching `assets/` — no version bump (manual run = dry run unless `dry_run: false`)
- `.github/scripts/svn-assets.sh` enforces the wp.org import limits before syncing (banner 4 MB, screenshot 10 MB, icon 1 MB, blueprint 100 KB, no zero-byte or misnamed files) — wp.org skips violations silently while the file stays reachable by URL, so the asset just never appears on the plugin page
