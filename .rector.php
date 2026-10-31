<?php

declare(strict_types=1);

return \Rector\Config\RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // Newer PHP sets would rewrite non-final classes, which is a BC risk. Keep them for v3.0.0.
    ->withPhpSets(php74: true)
    ->withPhpVersion(80200)
    ->withSets([
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::DEAD_CODE,
        \Art4\RectorBcLibrary\Set::BC_TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::PHP_POLYFILLS,
        \Rector\Set\ValueObject\SetList::PRIVATIZATION,
    ])
    // @see https://github.com/kbsali/php-redmine-api/issues/514
    ->withSkip([
        \Art4\RectorBcLibrary\Rector\BackwardCompatibleRector::class => [
            __DIR__ . '/src/Redmine/Client/NativeCurlClient.php',
            __DIR__ . '/tests/Unit/Api/AbstractApi/RetrieveDataTest.php',
            __DIR__ . '/tests/Unit/Api/ResponseValidationTraitTest.php',
        ],
        \Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveMixedDocblockOverruledByNativeTypeRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector::class,
    ])
;
