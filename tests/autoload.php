<?php

	function pluginApp(string $abstract, array $parameters = [])
	{
        return app($abstract, $parameters);
	}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

define('WORKSPACE', getenv('WORKSPACE') . '/');

require_once WORKSPACE. 'pl/bootstrap/autoload_testing.php';
