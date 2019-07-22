<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Tests\TestCase;

class AttributeFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $es = [];
        $vdiMapping = [];

        $this->assertEquals($es, $vdiMapping);
    }

}
