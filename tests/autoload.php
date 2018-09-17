<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

if (!defined('WORKSPACE')) {
	define('WORKSPACE', getenv('WORKSPACE') . '/');
}

require_once WORKSPACE. 'pl/bootstrap/autoload_testing.php';
