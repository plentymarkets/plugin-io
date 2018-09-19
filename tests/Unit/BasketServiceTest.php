<?php

namespace IO\Tests\Unit;

use Mockery\MockInterface;
use IO\Tests\TestCase;
use IO\Services\BasketService;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceTest extends TestCase
{

	protected $basketService;

    protected function setUp()
    {
        parent::setUp();
        $this->basketService = app(BasketService::class);
    }

    /** @test */
    public function it_gets_the_basket()
    {
        $foo = $this->basketService->getBasket();
        dd($foo);
    }


}
