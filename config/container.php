<?php

declare(strict_types = 1);

use Flowd\KochmodusWordpressPlugin\Application\Button\RenderButtonService;
use Flowd\KochmodusWordpressPlugin\Application\Settings\SettingsService;
use Flowd\KochmodusWordpressPlugin\Domain\Settings\SettingsRepositoryInterface;
use Flowd\KochmodusWordpressPlugin\Infrastructure\DependencyInjection\Container;
use Flowd\KochmodusWordpressPlugin\Infrastructure\Persistence\WordPressSettingsRepository;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\AdminMenuRegistrar;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\BlockRegistrar;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\EditorAssetsRegistrar;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\GlobalStylesRenderer;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\ScriptEnqueuer;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\SettingsPageRenderer;
use Flowd\KochmodusWordpressPlugin\Infrastructure\WordPress\ShortcodeRegistrar;

$container = new Container();

// Domain / Persistence
$container->set(SettingsRepositoryInterface::class, function (): WordPressSettingsRepository {
    return new WordPressSettingsRepository();
});

// Application
$container->set(SettingsService::class, function (Container $c): SettingsService {
    return new SettingsService($c->get(SettingsRepositoryInterface::class));
});

$container->set(RenderButtonService::class, function (): RenderButtonService {
    return new RenderButtonService();
});

// Infrastructure / WordPress
$container->set(ScriptEnqueuer::class, function (): ScriptEnqueuer {
    return new ScriptEnqueuer();
});

$container->set(SettingsPageRenderer::class, function (Container $c): SettingsPageRenderer {
    return new SettingsPageRenderer($c->get(SettingsService::class));
});

$container->set(GlobalStylesRenderer::class, function (Container $c): GlobalStylesRenderer {
    return new GlobalStylesRenderer($c->get(SettingsService::class));
});

$container->set(EditorAssetsRegistrar::class, function (Container $c): EditorAssetsRegistrar {
    return new EditorAssetsRegistrar($c->get(SettingsService::class));
});

$container->set(AdminMenuRegistrar::class, function (Container $c): AdminMenuRegistrar {
    return new AdminMenuRegistrar($c->get(SettingsPageRenderer::class));
});

$container->set(ShortcodeRegistrar::class, function (Container $c): ShortcodeRegistrar {
    return new ShortcodeRegistrar(
        $c->get(SettingsService::class),
        $c->get(RenderButtonService::class),
        $c->get(ScriptEnqueuer::class)
    );
});

$container->set(BlockRegistrar::class, function (Container $c): BlockRegistrar {
    return new BlockRegistrar(
        $c->get(SettingsService::class),
        $c->get(RenderButtonService::class),
        $c->get(ScriptEnqueuer::class)
    );
});

return $container;
