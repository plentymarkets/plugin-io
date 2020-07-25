<?php

use IO\Repositories\ItemWishListGuestRepository;
use IO\Repositories\ItemWishListRepository;
use IO\Services\ItemWishListService;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use PluginTests\SimpleTestCase;

/**
 * User: lukasmatzen
 * Date: 02.10.18
 */
class ItemWishListServiceTest extends SimpleTestCase
{
    /** @var ItemWishListService $wishListService */
    protected $wishListService;

    protected $itemWishListRepositoryMock;
    protected $itemWishListGuestRepositoryMock;
    protected $contactRepositoryMock;
    protected $sessionStorageRepositoryMock;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepositoryMock = Mockery::mock(ContactRepositoryContract::class);
        $this->replaceInstanceByMock(ContactRepositoryContract::class, $this->contactRepositoryMock);

        $this->sessionStorageRepositoryMock = Mockery::mock(SessionStorageRepositoryContract::class);
        $this->sessionStorageRepositoryMock->shouldReceive('getSessionValue')->with(
            SessionStorageRepositoryContract::GUEST_WISHLIST_MIGRATION
        )->andReturnFalse();
        $this->replaceInstanceByMock(SessionStorageRepositoryContract::class, $this->sessionStorageRepositoryMock);
    }

    /** @test */
    public function it_checks_if_the_wish_list_repo_is_an_instance_of_item_wish_list_repository()
    {
        $this->itemWishListRepositoryMock = Mockery::mock(ItemWishListRepository::class);
        $this->itemWishListGuestRepositoryMock = Mockery::mock(ItemWishListGuestRepository::class);

        $this->itemWishListRepositoryMock->shouldReceive('getItemWishList')->andReturn(true);
        $this->itemWishListGuestRepositoryMock->shouldReceive('getItemWishList')->andReturn(false);

        $this->replaceInstanceByMock(ItemWishListRepository::class, $this->itemWishListRepositoryMock);
        $this->replaceInstanceByMock(ItemWishListGuestRepository::class, $this->itemWishListGuestRepositoryMock);


        $this->contactRepositoryMock->shouldReceive('getContactId')->once()->andReturn(1);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->assertTrue($this->wishListService->getItemWishList());
    }

    /** @test */
    public function it_checks_if_the_wish_list_repo_is_an_instance_of_item_wish_list_guest_repository()
    {
        $this->itemWishListRepositoryMock = Mockery::mock(ItemWishListRepository::class);
        $this->itemWishListGuestRepositoryMock = Mockery::mock(ItemWishListGuestRepository::class);

        $this->replaceInstanceByMock(ItemWishListRepository::class, $this->itemWishListRepositoryMock);
        $this->replaceInstanceByMock(ItemWishListGuestRepository::class, $this->itemWishListGuestRepositoryMock);

        $this->itemWishListRepositoryMock->shouldReceive('getItemWishList')->andReturn(true);
        $this->itemWishListGuestRepositoryMock->shouldReceive('getItemWishList')->andReturn(false);

        $this->contactRepositoryMock->shouldReceive('getContactId')->once()->andReturn(0);

        $this->wishListService = pluginApp(ItemWishListService::class);

        $this->assertFalse($this->wishListService->getItemWishList());
    }
}
