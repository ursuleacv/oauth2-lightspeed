<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php81: true)
    ->withTypeCoverageLevel(0);
