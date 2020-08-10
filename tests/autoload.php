<?php

if (interface_exists(\Dotenv\Loader\LoaderInterface::class)) {
    $dotenv = \Dotenv\Dotenv::createMutable(__DIR__.'/../');
} else {
    $dotenv = Dotenv\Dotenv::create(__DIR__.'/../');
}
$dotenv->load();

if (getenv('TEST_SUITE_DIR')) {
    require_once getenv('TEST_SUITE_DIR') . '/tests/autoload.php';
}

require_once __DIR__ . '/../vendor/autoload.php';
