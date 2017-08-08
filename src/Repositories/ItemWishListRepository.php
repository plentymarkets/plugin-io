<?php

namespace IO\Repositories;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;
use IO\Services\CustomerService;
use IO\DBModels\ItemWishList;
use Plenty\Plugin\Application;


class ItemWishListRepository
{
    /** @var  DataBase */
    private $db;

    /** @var  CustomerService */
    private $customer;

    /**
     * ItemWishListRepository constructor.
     * @param DataBase $dataBase
     * @param CustomerService $customerService
     */
    public function __construct(DataBase $dataBase, CustomerService $customerService)
    {
        $this->db 		= $dataBase;
        $this->customer = $customerService;
    }

    /**
     * List all watched variationIds for contact
     * @return array
     */
    public function getItemWishList()
    {
        $variationIds = [];
        $plentyId = pluginApp(Application::class)->getPlentyID();

        /** @var Query $query */
        $query = $this->db->query(ItemWishList::NAMESPACE);

        $contactId = $this->customer->getContactId();

        if($contactId > 0)
        {
            $rows = $query->where('contactId', '=', $contactId)->where('plentyId', '=', $plentyId)->get();

            /** @var ItemWishList $wishListModel */
            foreach($rows as $wishListModel)
            {
                $variationIds[] = $wishListModel->variationId;
            }
        }

        return $variationIds;
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
        $contactId = $this->customer->getContactId();
        $plentyId = pluginApp(Application::class)->getPlentyID();

        if($variationId > 0)
        {
            $wishListEntry = $this->db->query(ItemWishList::NAMESPACE)->where('contactId', '=', $contactId)->where('variationId', '=', $variationId)->where('plentyId', '=', $plentyId)->get();
        }
        else
        {
            throw new \Exception('ItemWishListRepository::isItemInWishList - variationId undefined', 400);
        }

        if(!count($wishListEntry))
        {
            return false;
        }

        return true;
    }

    /**
     * @param int $variationId
     * @param int $quantity
     * @return mixed
     * @throws \Exception
     */
    public function addItemWishListEntry(int $variationId, int $quantity = 1)
    {
        $contactId = $this->customer->getContactId();
        $plentyId = pluginApp(Application::class)->getPlentyID();

        if($contactId > 0 && $variationId > 0)
        {
            $wishListModels = $this->db->query(ItemWishList::NAMESPACE)->where('contactId', '=', $contactId)->where('variationId', '=', $variationId)->where('plentyId', '=', $plentyId)->get();

            if(empty($wishListModels))
            {
                $wishListEntry = pluginApp(ItemWishList::class);
                $wishListEntry->contactId = $contactId;
                $wishListEntry->variationId = $variationId;
                $wishListEntry->plentyId = $plentyId;
                $wishListEntry->quantity = $quantity;
                $wishListEntry->createdAt = date("Y-m-d H:i:s");

                $createdWishListEntry = $this->db->save($wishListEntry);

                return pluginApp(ItemWishList::class)->fillByAttributes(json_decode(json_encode($createdWishListEntry), true));
            }
            else
            {
                return pluginApp(ItemWishList::class)->fillByAttributes(json_decode(json_encode($wishListModels[0]), true));
            }
        }
        else
        {
            throw new \Exception('ItemWishListRepository::addItemWishListEntry - user not logged in or variationId undefined', 401);
        }
    }

    /**
     * @param int $variationId
     * @return bool
     * @throws \Exception
     */
    public function removeItemWishListEntry(int $variationId)
    {
        $response = false;
        $contactId = $this->customer->getContactId();
        $plentyId = pluginApp(Application::class)->getPlentyID();

        if($contactId > 0 && $variationId > 0)
        {
            $wishListModels = $this->db->query(ItemWishList::NAMESPACE)->where('contactId', '=', $contactId)->where('variationId', '=', $variationId)->where('plentyId', '=', $plentyId)->get();

            foreach ($wishListModels as $wishListModel)
            {
                $response = $this->db->delete($wishListModel);
            }

            return $response;
        }
        else
        {
            throw new \Exception('ItemWishListRepository::removeItemWishListEntry - user not logged in or variationId undefined', 401);
        }
    }
}
