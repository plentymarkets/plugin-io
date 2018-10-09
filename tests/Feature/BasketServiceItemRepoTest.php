<?php

namespace IO\Tests\Feature;

use IO\Services\ItemSearch\Services\ItemSearchService;
use Mockery;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Legacy\Repositories\ItemDataLayerRepository;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\Variation\Models\Variation;
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

    protected $itemDataLayerRepoMock;

    protected function setUp()
    {
        parent::setUp();

        // $this->itemDataLayerRepoMock = Mockery::mock(ItemDataLayerRepositoryContract::class);
        // $this->app->instance(ItemDataLayerRepositoryContract::class , $this->itemDataLayerRepoMock);

        $this->basketService = pluginApp(BasketService::class);
        $this->variation = factory(Variation::class)->create([
            'minimumOrderQuantity' => 1.00
        ]);
        $this->variationStock = factory(VariationStock::class)->make([
           'varationId' => $this->variation->id,
           'warehouseId' => $this->variation->mainWarehouseId,
            'netStock' => 1000
        ]);

        // set referrer id in session
    }

    /** @test */
    public function it_adds_an_item_to_the_basket()
    {
        $variation = $this->variation;
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'referrerId' => 1, 'basketItemOrderParams' => [] ];

        $result = $this->basketService->addBasketItem($item1);

        $this->assertEquals($variation['id'], $result['data'][0]['variation']['id']);
        $this->assertEquals(1, $result['data']['quantity']);
        $this->assertCount(1, $result['data']);
    }

    /** @test */
    public function it_updates_an_item_in_the_basket()
    {
        $variation = $this->variations[0];
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'referrerId' => 1];

        $this->basketService->addBasketItem($item1);
        $result = $this->basketService->addBasketItem($item1);

        $this->assertEquals($variation['id'], $result['data'][0]['variation']['id']);
        $this->assertEquals(2, $result['data']['quantity']);
        $this->assertCount(1, $result['data']);
    }

    /** @test */
    public function it_removes_an_item_from_the_basket()
    {
        $variation = $this->variations[0];
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'referrerId' => 1];

        $basketItems = $this->basketService->addBasketItem($item1);
        $result = $this->basketService->deleteBasketItem($basketItems['data'][0]['id']);

        $this->assertEmpty($result['data']);
    }
}
