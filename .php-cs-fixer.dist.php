<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
    ])
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PER-CS3x0' => true,
        '@PER-CS3x0:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_indent' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arguments', 'arrays']], // Remove this rule after dropping support for PHP 7.4
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
