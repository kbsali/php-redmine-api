<?php

declare(strict_types=1);

return \Rector\Config\RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    //->withPhpSets()
    ->withPhp74Sets()
    ->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_74)
    ->withRules(\Art4\RectorBcLibrary\Set::withTypeCoverageLevel(50))
    ->withPreparedSets(
        true,
        true
    )
;
