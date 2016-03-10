<?php
$finder = (new \Symfony\Component\Finder\Finder())
    ->files()
    ->ignoreVCS(true)
    ->name('*.php')
    ->in(__DIR__ . '/lib/')
    ->in(__DIR__ . '/test/')
;

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        '-phpdoc_short_description',
        '-psr0',
        'header_comment',
        'long_array_syntax',
        'newline_after_open_tag',
        'no_useless_return',
        'ordered_use',
        'phpdoc_order',
    ])
    ->finder($finder)
;
