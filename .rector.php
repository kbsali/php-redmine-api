<?php

declare(strict_types=1);

return \Rector\Config\RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php82: true)
    ->withPhpVersion(80200)
    ->withSets([
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::DEAD_CODE,
        \Art4\RectorBcLibrary\Set::BC_TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::PHP_POLYFILLS,
        \Rector\Set\ValueObject\SetList::PRIVATIZATION,
    ])
;
