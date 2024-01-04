<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
    ])
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
