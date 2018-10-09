<?php

use IO\Constants\SessionStorageKeys;
use IO\DBModels\ItemWishList;
use IO\Repositories\ItemWishListGuestRepository;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Application;
use Tests\SimpleTestCase;

/**
 * User: lukasmatzen
 * Date: 02.10.18
 */
class ItemWishListGuestRepositoryTest extends SimpleTestCase {

    /** @var SessionStorageService $sessionStorageMock */
    protected $sessionStorageMock;

    /** @var Application $appMock */
    protected $appMock;

    /** @var ItemWishListGuestRepository $wishListGuestRepository */
    protected $wishListGuestRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->sessionStorageMock = Mockery::mock(SessionStorageService::class);
        app()->instance(SessionStorageService::class, $this->sessionStorageMock);

        $this->appMock = Mockery::mock(Application::class);
        $this->appMock->shouldReceive('getPlentyId')->andReturn(1);
        app()->instance(Application::class, $this->appMock);

        $this->wishListGuestRepository = pluginApp(ItemWishListGuestRepository::class);
    }

    /** @test */
    public function it_adds_an_item_to_an_empty_wish_list()
    {
        $this->sessionStorageMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST)
            ->andReturn(null);

        $this->sessionStorageMock
            ->shouldReceive('setSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST, Mockery::any())
            ->once()
            ->andReturn(null);

        $wishListEntry = $this->wishListGuestRepository->addItemWishListEntry(1000);

        $this->assertInstanceOf(ItemWishList::class, $wishListEntry);
        $this->assertEquals($wishListEntry->quantity, 1);
        $this->assertEquals($wishListEntry->plentyId, 1);
        $this->assertEquals($wishListEntry->variationId, 1000);
    }

    /** @test */
    public function it_adds_an_item_to_an_existing_wish_list()
    {
        $this->sessionStorageMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST)
            ->andReturn('{"1":{"1001":{"id":null,"contactId":0,"plentyId":"1","variationId":1001,"quantity":0,"createdAt":"2018-10-04 15:27:02"}}}');

        $this->sessionStorageMock
            ->shouldReceive('setSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST, Mockery::any())
            ->once()
            ->andReturn(null);

        $wishListEntry = $this->wishListGuestRepository->addItemWishListEntry(1000);

        $this->assertInstanceOf(ItemWishList::class, $wishListEntry);
        $this->assertEquals($wishListEntry->quantity, 1);
        $this->assertEquals($wishListEntry->plentyId, 1);
        $this->assertEquals($wishListEntry->variationId, 1000);
    }

    /** @test */
    public function it_adds_an_item_that_is_already_in_the_wish_list_to_the_wish_list()
    {
        $this->sessionStorageMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST)
            ->andReturn('{"1":{"1000":{"id":null,"contactId":0,"plentyId":"1","variationId":1000,"quantity":1,"createdAt":"2018-10-04 15:27:02"}}}');

        $this->sessionStorageMock
            ->shouldReceive('setSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST, Mockery::any())
            ->once()
            ->andReturn(null);

        $wishListEntry = $this->wishListGuestRepository->addItemWishListEntry(1000);

        $this->assertInstanceOf(ItemWishList::class, $wishListEntry);
        $this->assertEquals($wishListEntry->quantity, 2);
        $this->assertEquals($wishListEntry->plentyId, 1);
        $this->assertEquals($wishListEntry->variationId, 1000);
    }

    /** @test */
    public function it_deletes_an_item_from_the_wish_list()
    {
        $this->sessionStorageMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST)
            ->andReturn('{"1":{"1000":{"id":null,"contactId":0,"plentyId":"1","variationId":1000,"quantity":1,"createdAt":"2018-10-04 15:27:02"}}}');

        $this->sessionStorageMock
            ->shouldReceive('setSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST, Mockery::any())
            ->once()
            ->andReturn(null);

        $response = $this->wishListGuestRepository->removeItemWishListEntry(1000);

        $this->assertTrue($response);
    }

    /** @test */
    public function it_deletes_an_item_from_the_wish_list_wish_is_not_in_it()
    {
        $this->sessionStorageMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST)
            ->andReturn(null);

        $this->sessionStorageMock
            ->shouldReceive('setSessionValue')
            ->with(SessionStorageKeys::GUEST_WISHLIST, Mockery::any())
            ->andReturn(null);

        $response = $this->wishListGuestRepository->removeItemWishListEntry(1000);

        $this->assertFalse($response);
    }
}