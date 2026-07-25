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
    ->withPhpVersion(70400)
    ->withSets([
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::DEAD_CODE,
        \Art4\RectorBcLibrary\Set::BC_TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::PHP_POLYFILLS,
        \Rector\Set\ValueObject\SetList::EARLY_RETURN,
        \Rector\Set\ValueObject\SetList::INSTANCEOF,
        \Rector\Set\ValueObject\SetList::STRICT_BOOLEANS,
        \Rector\Set\ValueObject\SetList::PRIVATIZATION,
    ])
    ->withSkip([
        \Rector\TypeDeclaration\Rector\ClassMethod\ScalarParamTypeByMethodCallTypeRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ArrayParamTypeByMethodCallTypeRector::class,
        \Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector::class,
        \Rector\CodeQuality\Rector\BooleanNot\NegatedAndsToPositiveOrsRector::class,
    ])
;
