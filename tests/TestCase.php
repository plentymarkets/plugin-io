<?php

namespace IO\Tests;

require_once __DIR__ . '/TestHelper.php';

use Plenty\Modules\Plugin\PluginSet\Models\PluginSet;
use Plenty\Modules\System\Models\Webstore;
use PluginTests\BrowserKitTestCase;

/**
 * Class TestCase
 */
abstract class TestCase extends BrowserKitTestCase
{
    protected function setUp(): void
    {
		parent::setUp();

		// make sure, at least 1 plugin set exists
        if ( !PluginSet::all()->count() )
        {
            factory(PluginSet::class)->create();
        }

        // make sure at least default webstore with id 0 exists
	    if ( is_null(Webstore::find(0)) )
        {
            $pluginSetId = PluginSet::all()->first()->id;
            factory(Webstore::class)->create(["id" => 0, "pluginSetId" => $pluginSetId]);
        }

	}
}
