<?php

use Dotenv\Dotenv;
use Dotenv\Loader\LoaderInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$envDir = __DIR__ . '/../';

if (interface_exists(LoaderInterface::class)) {
    if (method_exists(Dotenv::class, 'createUnsafeMutable')) {
        Dotenv::createUnsafeMutable($envDir)->load();
    } else {
        Dotenv::createMutable($envDir)->load();
    }
} else {
    Dotenv::create($envDir)->load();
}

if (getenv('TEST_SUITE_DIR')) {
    require_once getenv('TEST_SUITE_DIR') . '/tests/autoload.php';
}

require_once __DIR__ . '/../vendor/autoload.php';
