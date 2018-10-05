<?php

use IO\Services\CustomerService;
use IO\Services\ItemWishListService;
use IO\Tests\TestCase;
use IO\DBModels\ItemWishList;

/**
 * User: lukasmatzen
 * Date: 02.10.18
 */
class ItemWishListTest extends TestCase {

    /** @var ItemWishListService $wishListService  */
    protected $wishListService;

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_adds_an_item_wishlist_entry_for_registered_user(){

        $variationId = 1;
        $quantity = 1;

        //ContactId must be > 0 for the registeredUserRepo
        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(1);

        app()->instance(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $itemWishlistModel = $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $this->assertNotNull($itemWishlistModel);
        $this->assertInstanceOf(ItemWishList::class, $itemWishlistModel);
        $this->assertEquals($variationId, $itemWishlistModel->variationId);
    }


    /**
     * @test
     */
    public function it_removes_an_item_wishlist_entry_for_registered_user(){

        $variationId = 1;
        $quantity = 1;

        //ContactId must be > 0 for the registeredUserRepo
        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(1);

        app()->instance(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $response = $this->wishListService->removeItemWishListEntry($variationId);

        $this->assertNotNull($response);
        $this->assertTrue($response);
    }

    /**
     * @test
     */
    public function it_removes_an_item_wishlist_entry_for_registered_user_with_invalid_variation_id(){

        $variationId = 1;
        $quantity = 1;

        //ContactId must be > 0 for the registeredUserRepo
        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(1);

        app()->instance(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $response = $this->wishListService->removeItemWishListEntry(2);

        $this->assertNotNull($response);
        $this->assertFalse($response);
    }
}