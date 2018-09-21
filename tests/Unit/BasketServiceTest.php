<?php

namespace IO\Tests\Unit;

use IO\Services\ItemSearch\Services\ItemSearchService;
use Mockery;
use Mockery\MockInterface;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Factories\BasketItemFactory;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Basket\Repositories\BasketItemRepository;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceTest extends TestCase
{

    /** @var BasketService $basketService  */
	protected $basketService;
	/** @var BasketItemRepository */
	protected $basketItemRepositoryMock;
    /** @var ItemSearchService */
	protected $itemSearchServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->basketItemRepositoryMock = Mockery::mock(BasketItemRepository::class);
        app()->instance(BasketItemRepository::class, $this->basketItemRepositoryMock);

        $this->itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
        app()->instance(ItemSearchService::class, $this->itemSearchServiceMock);


        $this->basketService = pluginApp(BasketService::class);


    }

    /** @test */
    public function it_gets_the_basket()
    {
        $this->basketService->getBasket();
    }

    /** @test */
    public function it_fills_the_basket_with_items()
    {
        //Fake Items
        $item1 = ['variationId' => 1, 'quantity' => 1, 'template' => 'test'];

        $basketItem = factory(BasketItem::class)->make([
            'id' => 1
        ]);

        $this->basketItemRepositoryMock->shouldReceive('findExistingOneByData')
            ->once()
            ->andReturn(null);

        $this->basketItemRepositoryMock->shouldReceive('all')
            ->once()
            ->andReturn([$basketItem]);

        $this->basketItemRepositoryMock->shouldReceive('addBasketItem')
            ->with(['variationId' => 1, 'quantity' => 1, 'template' => 'test', 'referrerId' => NULL])
            ->once()
            ->andReturn($item1);

        //never called yet
        $this->itemSearchServiceMock->shouldReceive('getResults')
            ->with()
            ->andReturn(
                ['documents' =>
                    ['data' =>
                        ['variation' => [
                            'id' => 1
                        ]]
                    ]
                ]);


        $this->basketService->addBasketItem($item1);
//        $this->basketService->getBasketItems();
    }


}
