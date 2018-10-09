<?php

namespace IO\Tests\Unit;

use Tests\SimpleTestCase;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class HelloWorldTest extends SimpleTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_says_hello_world()
    {
        $this->assertTrue(true);
    }


}
