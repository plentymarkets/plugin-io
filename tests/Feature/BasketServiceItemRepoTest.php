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
    protected $variations = [];

    protected function setUp()
    {
        parent::setUp();

        $this->basketService = pluginApp(BasketService::class);
        $variations[] = factory(VariationBaseFactory::class)->create();

        // set referrer id in session
    }

    /** @test */
    public function it_adds_an_item_to_the_basket()
    {
        $variation = $this->variations[0];
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'referrerId' => 1];

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
