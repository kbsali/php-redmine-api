<?php

// use Composer if possible
$composerPath = __DIR__.'/../vendor/autoload.php';

if (file_exists($composerPath)) {
    include $composerPath;
    return;
}

/*
 * PSR-4 implementation for Redmine
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix and base directory with trailing /
    $namespace_map = [
        'Redmine\\'        => __DIR__.'/Redmine/',
        'Redmine\\Tests\\' => __DIR__.'/../tests/',
    ];

    foreach ($namespace_map as $prefix => $base_dir) {
        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader
            continue;
        }

        // get the relative class name
        $relative_class = substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
            break;
        }
    }
});
