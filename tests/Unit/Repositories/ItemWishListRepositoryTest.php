<?php

use IO\DBModels\ItemWishList;
use IO\Repositories\ItemWishListRepository;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use PluginTests\SimpleTestCase;

/**
 * Created by PhpStorm.
 * User: lukasmatzen
 * Date: 02.10.18
 * Time: 15:39
 */
class ItemWishListRepositoryTest extends SimpleTestCase
{
    /**
     * @var ItemWishListRepository $itemWishListRepository
     */
    protected $itemWishListRepository;

    protected $databaseMock;
    protected $databaseQueryMock;
    protected $customerServiceMock;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseMock        = $this->mockDatabase();
        $this->databaseQueryMock   = $this->mockQuery();
        $this->contactRepository = Mockery::mock(ContactRepositoryContract::class);

        $this->replaceInstanceByMock(ContactRepositoryContract::class, $this->contactRepository);

        $this->itemWishListRepository = pluginApp(ItemWishListRepository::class);

    }


    private function getItemWishListModel()
    {
        $wishListEntry              = pluginApp(ItemWishList::class);
        $wishListEntry->contactId   = 1;
        $wishListEntry->variationId = 1;
        $wishListEntry->plentyId    = "1000";
        $wishListEntry->quantity    = "1";
        $wishListEntry->createdAt   = date("Y-m-d H:i:s");

        return $wishListEntry;
    }

    /**
     * @test
     */
    public function it_fills_item_wish_list_with_empty_db_result()
    {
        /**
         * @var ItemWishList $itemWishListModel
         */
        $itemWishListModel = $this->getItemWishListModel();
        $this->databaseMock->shouldReceive('query')->andReturn($this->databaseQueryMock);
        $this->databaseMock->shouldReceive('save')->with(Mockery::any())->andReturn($itemWishListModel);
        $this->databaseQueryMock->shouldReceive('where')->with(Mockery::any(), Mockery::any(),
            Mockery::any())->andReturn($this->databaseQueryMock);
        $this->databaseQueryMock->shouldReceive('get')->andReturn([]);
        $this->contactRepository->shouldReceive('getContactId')->andReturn(1);


        /**
         * @var ItemWishList $response
         */
        $response = $this->itemWishListRepository->addItemWishListEntry(1);

        $this->assertNotNull($response);
        $this->assertInstanceOf(ItemWishList::class, $response);
        $this->assertEquals($itemWishListModel->contactId, $response->contactId);
        $this->assertEquals($itemWishListModel->plentyId, $response->plentyId);
        $this->assertEquals($itemWishListModel->variationId, $response->variationId);

    }

    /**
     * @test
     */
    public function it_fills_item_wish_list_with_db_result()
    {
        /**
         * @var ItemWishList $itemWishListModel
         */
        $itemWishListModel = $this->getItemWishListModel();
        $this->databaseMock->shouldReceive('query')->andReturn($this->databaseQueryMock);
        $this->databaseMock->shouldReceive('save')->with(Mockery::any())->andReturn($itemWishListModel);
        $this->databaseQueryMock->shouldReceive('where')->with(Mockery::any(), Mockery::any(),
            Mockery::any())->andReturn($this->databaseQueryMock);
        $this->databaseQueryMock->shouldReceive('get')->andReturn([$itemWishListModel]);
        $this->contactRepository->shouldReceive('getContactId')->andReturn(1);


        /**
         * @var ItemWishList $response
         */
        $response = $this->itemWishListRepository->addItemWishListEntry(1);

        $this->assertNotNull($response);
        $this->assertInstanceOf(ItemWishList::class, $response);
        $this->assertEquals($itemWishListModel->contactId, $response->contactId);
        $this->assertEquals($itemWishListModel->plentyId, $response->plentyId);
        $this->assertEquals($itemWishListModel->variationId, $response->variationId);

    }

    /**
     * @test
     */
    public function it_gets_an_exception_by_method_add_item_wish_list_entry()
    {
        $this->contactRepository->shouldReceive('getContactId')->andReturn(0);
        $this->expectException(\Exception::class);
        $this->itemWishListRepository->addItemWishListEntry(0);
    }

    /**
     * @test
     */
    public function it_gets_an_exception_by_method_remove_item_wish_list_entry()
    {
        $this->contactRepository->shouldReceive('getContactId')->andReturn(0);
        $this->expectException(\Exception::class);
        $this->itemWishListRepository->removeItemWishListEntry(0);
    }


    /**
     * @test
     */
    public function it_removes_an_wish_list_item_with_no_db_entry()
    {
        /**
         * @var ItemWishList $itemWishListModel
         */

        $this->databaseMock->shouldReceive('query')->andReturn($this->databaseQueryMock);
        $this->databaseQueryMock->shouldReceive('where')->with(Mockery::any(), Mockery::any(),
            Mockery::any())->andReturn($this->databaseQueryMock);
        $this->databaseQueryMock->shouldReceive('get')->andReturn([]);
        $this->contactRepository->shouldReceive('getContactId')->andReturn(1);


        /**
         * @var ItemWishList $response
         */
        $response = $this->itemWishListRepository->removeItemWishListEntry(1);

        $this->assertNotNull($response);
        $this->assertFalse($response);

    }

    /**
     * @test
     */
    public function it_removes_an_wish_list_item_with_db_entry()
    {
        /**
         * @var ItemWishList $itemWishListModel
         */

        $itemWishListModel = $this->getItemWishListModel();

        $this->databaseMock->shouldReceive('query')->andReturn($this->databaseQueryMock);
        $this->databaseMock->shouldReceive('delete')->andReturn(true);
        $this->databaseQueryMock->shouldReceive('where')->with(Mockery::any(), Mockery::any(),
            Mockery::any())->andReturn($this->databaseQueryMock);
        $this->databaseQueryMock->shouldReceive('get')->andReturn([$itemWishListModel]);
        $this->contactRepository->shouldReceive('getContactId')->andReturn(1);


        /**
         * @var ItemWishList $response
         */
        $response = $this->itemWishListRepository->removeItemWishListEntry(1);

        $this->assertNotNull($response);
        $this->assertTrue($response);

    }


}
