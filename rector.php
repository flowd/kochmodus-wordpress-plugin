<?php

declare(strict_types=1);

use Fsylum\RectorWordPress\Set\WordPressLevelSetList;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config',
        __DIR__ . '/kochmodus.php',
        __DIR__ . '/uninstall.php',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        __DIR__ . '/node_modules',
        __DIR__ . '/blocks/build',
    ])
    ->withPhpVersion(PhpVersion::PHP_74)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSets([
        WordPressLevelSetList::UP_TO_WP_6_8,
    ])
    ->withCache(__DIR__ . '/.rector-cache');
