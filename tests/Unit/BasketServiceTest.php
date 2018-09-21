<?php

namespace IO\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Repositories\BasketItemRepository;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceTest extends TestCase
{

    /**
     * @var BasketService $basketService
     */
	protected $basketService;
	protected $basketItemRepositoryMock;

    protected function setUp()
    {
        parent::setUp();

        $this->basketItemRepositoryMock = Mockery::mock(BasketItemRepository::class);
        app()->instance(BasketItemRepository::class, $this->basketItemRepositoryMock);


        $this->basketService = pluginApp(BasketService::class);


    }

    /** @test */
    public function it_gets_the_basket()
    {
        $foo = $this->basketService->getBasket();
        dd($foo);
    }

    /**
     * @test
     */
    public function it_fills_the_basket_with_items()
    {
        //Fake Items
        $item1 = ['variationId' => 1, 'quantity' => 1, 'template' => 'test'];

        $this->basketItemRepositoryMock->shouldReceive('findExistingOneByData')
            ->once()
            ->andReturn(null);

        $this->basketItemRepositoryMock->shouldReceive('addBasketItem')
            ->with()
            ->once()
            ->andReturn($item1);


        $this->basketService->addBasketItem($item1);

        $this->basketService->getBasketItems();



    }


}
