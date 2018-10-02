<?php

namespace IO\Tests\Unit;

use Mockery\MockInterface;
use IO\Tests\TestCase;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class HelloWorldTest extends TestCase
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
