<?php

namespace IO\Tests\Feature;

use IO\Services\ItemSearch\Services\ItemSearchService;
use Mockery;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Hooks\BasketItem\CheckNewItemQuantity;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\Stock\Hooks\CheckItemStock;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Basket\Models\Basket;

use Illuminate\Support\Facades\Session;
use Plenty\Modules\Item\VariationStock\Models\VariationStock;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceItemRepoTest extends TestCase
{
    /** @var BasketService $basketService  */
    protected $basketService;
    protected $variation;
    protected $variationStock;

    /** @var ItemSearchService $itemSearchServiceMock  */
    protected $itemSearchServiceMock;

    protected $basketRepoMock;

    protected function setUp()
    {
       parent::setUp();

       $checkItemStockMockery = Mockery::mock(CheckItemStock::class);
       $checkItemStockMockery->shouldReceive('handle')->andReturn();
       app()->instance(CheckItemStock::class, $checkItemStockMockery);

        $checkNewItemQuantityMockery = Mockery::mock(CheckNewItemQuantity::class);
        $checkNewItemQuantityMockery->shouldReceive('handle')->andReturn();
        app()->instance(CheckNewItemQuantity::class, $checkNewItemQuantityMockery);


        $this->itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
        app()->instance(ItemSearchService::class, $this->itemSearchServiceMock);

        $this->basketService = pluginApp(BasketService::class);
        $this->variation = factory(Variation::class)->create([
            'minimumOrderQuantity' => 1.00
        ]);

        $esMockData = $this->getTestJsonData();
        $esMockData['documents'][0]['id'] =  $this->variation['id'];

        $this->itemSearchServiceMock
            ->shouldReceive('getResults')
            ->with(Mockery::any())//BasketItems::getSearchFactory(['variationIds' => [$variationId],'quantities' => [$variationId => 1]])
            ->andReturn($esMockData);

        $basket = factory(Basket::class)->create();
        Session::shouldReceive('getId')
            ->andReturn($basket->sessionId);

    }

    /** @test */
    public function it_adds_an_item_to_the_basket()
    {

        $item1 = ['variationId' => $this->variation['id'], 'quantity' => 1, 'template' => '', 'basketItemOrderParams' => [] ];


        $result = $this->basketService->addBasketItem($item1);

        $this->assertEquals($this->variation['id'], $result[0]['variationId']);
        $this->assertEquals(1, $result[0]['quantity']);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_updates_an_item_in_the_basket()
    {
        $item1 = ['variationId' => $this->variation['id'], 'quantity' => 1, 'template' => ''];

        $this->basketService->addBasketItem($item1);
        $result = $this->basketService->addBasketItem($item1);

        $this->assertEquals($this->variation['id'], $result[0]['variationId']);
        $this->assertEquals(2, $result[0]['quantity']);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_removes_an_item_from_the_basket()
    {
        $item1 = ['variationId' => $this->variation['id'], 'quantity' => 1, 'template' => ''];

        $basketItems = $this->basketService->addBasketItem($item1);
        $result = $this->basketService->deleteBasketItem($basketItems[0]['id']);

        $this->assertEmpty($result);
    }

    /**
     * helper method to get the item search result json
     * @return mixed
     */
    public function getTestJsonData()
    {
        $file = __DIR__ . "/../Fixtures/complete_basket_response.json";
        return json_decode(
            file_get_contents($file),
            true
        );
    }
}
