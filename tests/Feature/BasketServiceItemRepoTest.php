<?php

namespace IO\Tests\Feature;

use IO\Services\ItemSearch\Services\ItemSearchService;
use Mockery;
use IO\Tests\TestCase;
use IO\Services\BasketService;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceItemRepoTest extends TestCase
{

    /** @var BasketService $basketService  */
    protected $basketService;
    /** @var ItemSearchService */
    protected $itemSearchServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
        app()->instance(ItemSearchService::class, $this->itemSearchServiceMock);


        $this->basketService = pluginApp(BasketService::class);


    }

    /** @test */
    public function it_fills_the_basket_with_items_and_check_it()
    {
        //Fake Items

        $baseFactory = factory(BaseFactory::class)->create();

        $item1 = ['variationId' => 1037, 'quantity' => 1, 'template' => 'test'];



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
