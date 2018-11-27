<?php

namespace IO\Tests\Feature;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\BasketItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Mockery;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Hooks\BasketItem\CheckNewItemQuantity;
use Plenty\Modules\Cloud\ElasticSearch\Factories\ElasticSearchResultFactory;
use Plenty\Modules\Item\Stock\Hooks\CheckItemStock;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Basket\Models\Basket;
use Illuminate\Foundation\Testing\RefreshDatabase;


use Illuminate\Support\Facades\Session;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceItemRepoTest extends TestCase
{
    use RefreshDatabase;


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

        $this->createApplication();

       $checkItemStockMockery = Mockery::mock(CheckItemStock::class);
       $checkItemStockMockery->shouldReceive('handle')->andReturn();
       $this->replaceInstanceByMock(CheckItemStock::class, $checkItemStockMockery);

       $checkNewItemQuantityMockery = Mockery::mock(CheckNewItemQuantity::class);
       $checkNewItemQuantityMockery->shouldReceive('handle')->andReturn();
       $this->replaceInstanceByMock(CheckNewItemQuantity::class, $checkNewItemQuantityMockery);


       $this->itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
       $this->replaceInstanceByMock(ItemSearchService::class, $this->itemSearchServiceMock);

       $this->basketService = pluginApp(BasketService::class);
       $this->variation = factory(Variation::class)->create([
            'minimumOrderQuantity' => 1.00
       ]);

       /** @var ElasticSearchResultFactory $esFactory */
       $esFactory = pluginApp(ElasticSearchResultFactory::class);
       $esMockData = $esFactory->makeWrapped();
       $esMockData['documents'][0]['id'] =  $this->variation['id'];
       $esMockData['documents'][0]['data']['variation']['id'] = $this->variation['id'];

       /**
       * @var ResultFieldTemplate $resultFieldTemplate
       */
       $resultFieldTemplate = pluginApp(ResultFieldTemplate::class);
       $resultFieldTemplate->setTemplates([ResultFieldTemplate::TEMPLATE_BASKET_ITEM   => 'Ceres::ResultFields.BasketItem']);
       $this->replaceInstanceByMock(ResultFieldTemplate::class, $resultFieldTemplate);

       $basketItemsMock = Mockery::mock(BasketItems::class);
       $basketItemsMock->shouldReceive('getSearchFactory')->with([])->andReturn([]);
       $this->replaceInstanceByMock(BasketItems::class, $basketItemsMock);

       $this->itemSearchServiceMock
            ->shouldReceive('getResults')
            ->with(Mockery::any())
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
}
