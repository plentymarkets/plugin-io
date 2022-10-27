<?php

namespace IO\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IO\DBModels\ItemWishList;
use IO\Services\CustomerService;
use IO\Services\ItemWishListService;
use IO\Tests\TestCase;
use Mockery;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Application;

/**
 * User: lukasmatzen
 * Date: 02.10.18
 */
class ItemWishListTest extends TestCase
{
    use RefreshDatabase;

    /** @var ItemWishListService $wishListService */
    protected $wishListService;

    protected $plentyId;

    /** @var SessionStorageRepositoryContract $sessionStorageRepository */
    protected $sessionStorageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plentyId = pluginApp(Application::class)->getPlentyId();
        $this->sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);
    }

    /**
     * @test
     */
    public function it_adds_an_item_wishlist_entry_for_registered_user()
    {
        $variationId = 1;
        $quantity = 1;

        //ContactId must be > 0 for the registeredUserRepo
        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(1);

        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $itemWishlistModel = $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $this->assertNotNull($itemWishlistModel);
        $this->assertInstanceOf(ItemWishList::class, $itemWishlistModel);
        $this->assertEquals($variationId, $itemWishlistModel->variationId);
    }


    /**
     * @test
     */
    public function it_removes_an_item_wishlist_entry_for_registered_user()
    {
        $variationId = 1;
        $quantity = 1;

        //ContactId must be > 0 for the registeredUserRepo
        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(1);

        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $response = $this->wishListService->removeItemWishListEntry($variationId);

        $this->assertNotNull($response);
        $this->assertTrue($response);
    }

    /**
     * @test
     */
    public function it_removes_an_item_wishlist_entry_for_registered_user_with_invalid_variation_id()
    {
        $variationId = 1;
        $quantity = 1;

        //ContactId must be > 0 for the registeredUserRepo
        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(1);

        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $response = $this->wishListService->removeItemWishListEntry(2);

        $this->assertNotNull($response);
        $this->assertFalse($response);
    }

    /** @test */
    public function it_adds_an_item_to_the_wish_list_as_guest()
    {
        $variationId = 1;
        $quantity = 1;

        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(0);
        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $response = $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $wihsList = json_decode($this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST), true);
        $whishListItem = $wihsList[$this->plentyId][$variationId];

        $this->assertNotNull($whishListItem);
        $this->assertInstanceOf(ItemWishList::class, $response);
        $this->assertEquals($variationId, $response->variationId);
        $this->assertEquals($variationId, $response->quantity);
        $this->assertEquals($variationId, $whishListItem['variationId']);
        $this->assertEquals($variationId, $whishListItem['quantity']);
    }

    /** @test */
    public function it_adds_an_item_to_the_wish_list_as_guest_which_is_already_in_the_wish_list()
    {
        $variationId = 1;
        $quantity = 1;

        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(0);
        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->wishListService->addItemWishListEntry($variationId, $quantity);
        $response = $this->wishListService->addItemWishListEntry($variationId, $quantity);

        $wishList = json_decode($this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST), true);
        $whishListItem = $wishList[$this->plentyId][$variationId];

        $this->assertNotNull($whishListItem);
        $this->assertInstanceOf(ItemWishList::class, $response);
        $this->assertEquals($variationId, $response->variationId);
        $this->assertEquals(2, $response->quantity);
        $this->assertEquals($variationId, $whishListItem['variationId']);
        $this->assertEquals(2, $whishListItem['quantity']);
    }

    /** @test */
    public function it_removes_an_item_from_the_wish_list_as_guest()
    {
        $variationId = 1;
        $quantity = 1;
        $whishListItem = null;

        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(0);
        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->wishListService->addItemWishListEntry($variationId, $quantity);
        $response = $this->wishListService->removeItemWishListEntry($variationId);

        $wishList = json_decode($this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST), true);

        if (array_key_exists($variationId, $wishList[$this->plentyId]))
        {
            $whishListItem = $wishList[$this->plentyId][$variationId];
        }

        $this->assertNotNull($wishList[$this->plentyId]);
        $this->assertNull($whishListItem);
        $this->assertTrue($response);
    }

    /** @test */
    public function it_removes_an_item_from_the_wish_list_as_guest_which_is_not_in_the_wish_list()
    {
        $variationId = 1;

        $customerServiceMock = Mockery::mock(CustomerService::class);
        $customerServiceMock->shouldReceive('getContactId')->andReturn(0);
        $this->replaceInstanceByMock(CustomerService::class, $customerServiceMock);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $response = $this->wishListService->removeItemWishListEntry($variationId);

        $wishList = json_decode($this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST), true);
        $whishListItem = $wishList[$this->plentyId][$variationId] ?? null;

        $this->assertNull($whishListItem);
        $this->assertFalse($response);
    }
}
