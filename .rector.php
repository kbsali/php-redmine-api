<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    //->withPhpSets()
    ->withPhp74Sets()
    ->withPhpVersion(70400)
    ->withTypeCoverageLevel(21)
    ->withPreparedSets(
        true,
        true
    )
    ->withSets([
        \Art4\RectorBcLibrary\Set::SET,
    ])
;
