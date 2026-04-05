<?php

declare(strict_types=1);

$finder = \PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
    ])
;

return (new \PhpCsFixer\Config())
    ->setRules([
        '@PER-CS3x0' => true,
        '@PER-CS3x0:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'no_empty_phpdoc' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_order_by_value' => true,
        'phpdoc_order' => true,
        'phpdoc_param_order' => true,
        'phpdoc_separation' => ['skip_unlisted_annotations' => false],
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_no_duplicates' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arguments', 'arrays']], // Remove this rule after dropping support for PHP 7.4
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
