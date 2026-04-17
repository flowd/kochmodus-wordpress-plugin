<?php
/**
 * Plugin Name:       Kochmodus
 * Plugin URI:        https://kochmodus.de
 * Description:       Embeds the Kochmodus cooking mode widget on recipe pages.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Flowd GmbH
 * Author URI:        https://flowd.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kochmodus
 * Domain Path:       /languages
 */

declare(strict_types = 1);

if (!defined('ABSPATH')) {
    exit;
}

define('KOCHMODUS_VERSION', '1.0.0');
define('KOCHMODUS_PLUGIN_FILE', __FILE__);
define('KOCHMODUS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once __DIR__ . '/vendor/autoload.php';

/** @var \Kochmodus\Infrastructure\DependencyInjection\Container $container */
$container = require __DIR__ . '/config/container.php';

// Activation / Deactivation
register_activation_hook(__FILE__, [\Kochmodus\Infrastructure\WordPress\Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [\Kochmodus\Infrastructure\WordPress\Plugin::class, 'deactivate']);

// Boot
$plugin = new \Kochmodus\Infrastructure\WordPress\Plugin($container);
$plugin->boot();
