<?php
/**
 * Created by IntelliJ IDEA.
 * User: ihussein
 * Date: 01.08.17
 */

namespace IO\Services;


use IO\Repositories\ItemWishListRepository;

/**
 * Class WishListService
 * @package IO\Services
 */
class ItemWishListService
{
    /**
     * @var ItemWishListRepository
     */
    private $itemWishList;

    public function __construct(ItemWishListRepository $itemWishListRepo)
    {
        $this->itemWishList = $itemWishListRepo;
    }

    /**
     * @param int $variationId
     * @param int $quantity
     * @return mixed
     */
    public function addItemWishListEntry(int $variationId, int $quantity)
    {
        return $this->itemWishList->addItemWishListEntry($variationId, $quantity);
    }

    /**
     * @param int $variationId
     * @return bool
     */
    public function isItemInWishList(int $variationId)
    {
        return $this->itemWishList->isItemInWishList($variationId);
    }

    /**
     * @return array
     */
    public function getItemWishListForContact()
    {
        return $this->itemWishList->getItemWishListForContact();
    }

    /**
     * @return int
     */
    public function getCountedItemWishListForContact()
    {
        return$this->itemWishList->getCountedItemWishListForContact();
    }

    /**
     * @param int $variationId
     * @return bool
     */
    public function removeItemWishListEntry(int $variationId)
    {
        return $this->itemWishList->removeItemWishListEntry($variationId);
    }
}
