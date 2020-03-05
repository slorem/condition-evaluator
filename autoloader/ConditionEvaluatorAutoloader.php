<?php

spl_autoload_register(function($class) {
    $prefix = 'Slorem\\';
    
    if (0 !== substr_compare($class, $prefix, 0, strlen($prefix))) {
        return null;
    }
    
    $sourceDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    
    if (file_exists($sourceDir . $fileName)) {
        require_once $sourceDir . $fileName;
    }
});

