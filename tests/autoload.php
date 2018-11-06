<?php

$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

if (getenv('TEST_SUITE_DIR')) {
    require_once getenv('TEST_SUITE_DIR') . '/tests/autoload.php';
}

require_once __DIR__ . '/../vendor/autoload.php';
