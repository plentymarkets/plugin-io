<?php

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../vendor/autoload.php';

if (getenv('TEST_SUITE_DIR')) {
    require_once getenv('TEST_SUITE_DIR') . '/tests/autoload.php';
}