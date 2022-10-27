<?php

namespace IO\Repositories;

use IO\Services\SessionStorageService;
use IO\DBModels\ItemWishList;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Application;

class ItemWishListGuestRepository
{
    /** @var  SessionStorageRepositoryContract */
    private $sessionStorageRepository;

    private $plentyId;

    /**
     * ItemWishListGuestRepository constructor.
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     * @param Application $app
     */
    public function __construct(SessionStorageRepositoryContract $sessionStorageRepository, Application $app)
    {
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->plentyId       = $app->getPlentyId();
    }

    /**
     * List all watched variationIds for contact
     * @return array
     */
    public function getItemWishList()
    {
        $wishList = $this->getItemWishListForAllPlentyIds();
        $variationIds = [];

        if(isset($wishList))
        {
            $variationIds = array_keys($wishList[$this->plentyId]);
        }

        return $variationIds;
    }

    public function getItemWishListWithData()
    {
        $wishList = $this->getItemWishListForAllPlentyIds();
        return $wishList[$this->plentyId];
    }

    public function getItemWishListForAllPlentyIds()
    {
        return json_decode($this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST), true);
    }

    /**
     * Get count WishList entries
     * @return int
     */
    public function getCountedItemWishList()
    {
        return count($this->getItemWishList());
    }

    /**
     * @param int $variationId
     * @return bool
     */
    public function isItemInWishList(int $variationId = 0)
    {
        $wishList = $this->getItemWishList();

        if(!is_array($wishList))
        {
            return false;
        }
        else
        {
            return in_array($variationId, $wishList);
        }
    }

    /**
     * @param int $variationId
     * @param int $quantity
     * @return mixed
     */
    public function addItemWishListEntry(int $variationId, int $quantity = 1)
    {
        $wishListEntry = pluginApp(ItemWishList::class);
        $wishListEntry->contactId = 0;
        $wishListEntry->variationId = $variationId;
        $wishListEntry->plentyId = $this->plentyId;
        $wishListEntry->quantity = $quantity;
        $wishListEntry->createdAt = date("Y-m-d H:i:s");

        $wishListComplete = $this->getItemWishListForAllPlentyIds();
        if (is_array($wishListComplete)) {
            $wishList = $wishListComplete[$this->plentyId] ?? [];
        }

        if($this->isItemInWishList($variationId))
        {
            $wishListEntry->quantity += $wishList[$variationId]['quantity'] ?? 0;
        }

        $wishListComplete[$this->plentyId][$variationId] = $wishListEntry;
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST, json_encode($wishListComplete));

        return $wishListEntry;
    }

    /**
     * @param int $variationId
     * @return bool - true if variation was found and deleted. false if no variation was found.
     */
    public function removeItemWishListEntry(int $variationId)
    {
        $wishListComplete = $this->getItemWishListForAllPlentyIds();

        if(isset($wishListComplete) && array_key_exists($variationId, $wishListComplete[$this->plentyId]))
        {
            unset($wishListComplete[$this->plentyId][$variationId]);
            $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST, json_encode($wishListComplete));
            return true;
        }

        return false;
    }

    public function resetItemWishList()
    {
        $wishListComplete = $this->getItemWishListForAllPlentyIds();
        $wishListComplete[$this->plentyId] = [];
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST, json_encode($wishListComplete));
    }
}
