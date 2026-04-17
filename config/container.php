<?php

declare(strict_types = 1);

use Kochmodus\Application\Button\RenderButtonService;
use Kochmodus\Application\Settings\SettingsService;
use Kochmodus\Domain\Settings\SettingsRepositoryInterface;
use Kochmodus\Infrastructure\DependencyInjection\Container;
use Kochmodus\Infrastructure\Persistence\WordPressSettingsRepository;
use Kochmodus\Infrastructure\WordPress\AdminMenuRegistrar;
use Kochmodus\Infrastructure\WordPress\BlockRegistrar;
use Kochmodus\Infrastructure\WordPress\ScriptEnqueuer;
use Kochmodus\Infrastructure\WordPress\SettingsPageRenderer;
use Kochmodus\Infrastructure\WordPress\ShortcodeRegistrar;

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
