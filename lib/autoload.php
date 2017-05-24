<?php

spl_autoload_register(function ($class) {
    /* All of the classes have names like "Redmine\Foo", so we need to
     * replace the backslashes with frontslashes if we want the name
     * to map directly to a location in the filesystem.
     */
    $class = str_replace('\\', '/', $class);

    // Check under the current directory
    $path = dirname(__FILE__).'/'.$class.'.php';
    if (file_exists($path)) {
        require_once $path;
    }
});
