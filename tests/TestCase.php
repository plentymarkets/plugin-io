<?php

namespace IO\Tests;

require_once __DIR__ . '/TestHelper.php';

use PluginTests\BrowserKitTestCase;

/**
 * Class TestCase
 */
abstract class TestCase extends BrowserKitTestCase
{
    protected function setUp()
	{
		parent::setUp();
	}
}