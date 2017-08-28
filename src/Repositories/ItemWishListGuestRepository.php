<?php

namespace IO\Repositories;

use IO\Constants\SessionStorageKeys;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;
use IO\Services\SessionStorageService;
use IO\Services\CustomerService;
use IO\DBModels\ItemWishList;
use Plenty\Plugin\Application;

class ItemWishListGuestRepository
{
    /** @var  SessionStorageService */
    private $sessionStorage;
    
    private $plentyId;
    
    /**
     * ItemWishListGuestRepository constructor.
     * @param SessionStorageService $sessionStorage
     * @param Application $app
     */
    public function __construct(SessionStorageService $sessionStorage, Application $app)
    {
        $this->sessionStorage = $sessionStorage;
        $this->plentyId       = $app->getPlentyId();
    }
    
    /**
     * List all watched variationIds for contact
     * @return array
     */
    public function getItemWishList()
    {
        $wishList = $this->getItemWishListForAllPlentyIds();
        $variationIds = array_keys($wishList[$this->plentyId]);
    
        if(is_null($variationIds))
        {
            $variationIds = [];
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
        return json_decode($this->sessionStorage->getSessionValue(SessionStorageKeys::GUEST_WISHLIST), true);
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function addItemWishListEntry(int $variationId, int $quantity = 1)
    {
        $wishListEntry = pluginApp(ItemWishList::class);
        $wishListEntry->contactId = 0;
        $wishListEntry->variationId = $variationId;
        $wishListEntry->plentyId = $this->plentyId;
        $wishListEntry->quantity = $quantity;
        $wishListEntry->createdAt = date("Y-m-d H:i:s");
        
        $wishList = $this->getItemWishListWithData();
        $wishListComplete = $this->getItemWishListForAllPlentyIds();
        
        if($this->isItemInWishList($variationId))
        {
            $wishListEntry->quantity += $wishList[$variationId]['quantity'];
        }
        
        $wishListComplete[$this->plentyId][$variationId] = $wishListEntry;
        $this->sessionStorage->setSessionValue(SessionStorageKeys::GUEST_WISHLIST, json_encode($wishListComplete));
        
        return $wishListEntry;
    }
    
    /**
     * @param int $variationId
     * @return bool
     * @throws \Exception
     */
    public function removeItemWishListEntry(int $variationId)
    {
        $wishListComplete = $this->getItemWishListForAllPlentyIds();
        
        if(array_key_exists($variationId, $wishListComplete[$this->plentyId]))
        {
            unset($wishListComplete[$this->plentyId][$variationId]);
        }
        
        $this->sessionStorage->setSessionValue(SessionStorageKeys::GUEST_WISHLIST, json_encode($wishListComplete));
        
        return true;
    }
    
    public function resetItemWishList()
    {
        $wishListComplete = $this->getItemWishListForAllPlentyIds();
        $wishListComplete[$this->plentyId] = [];
        $this->sessionStorage->setSessionValue(SessionStorageKeys::GUEST_WISHLIST, json_encode($wishListComplete));
    }
}
