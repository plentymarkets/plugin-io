<?php

namespace IO\Repositories;

use IO\Helper\Utils;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;
use IO\DBModels\ItemWishList;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationList;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;


class ItemWishListRepository
{
    /** @var  DataBase */
    private $db;

    /** @var  ContactRepositoryContract $contactRepository */
    private $contactRepository;

    /**
     * ItemWishListRepository constructor.
     * @param DataBase $dataBase
     * @param ContactRepositoryContract $contactRepository
     */
    public function __construct(DataBase $dataBase, ContactRepositoryContract $contactRepository)
    {
        $this->db = $dataBase;
        $this->contactRepository = $contactRepository;
    }

    /**
     * List all watched variationIds for contact
     * @return array
     */
    public function getItemWishList()
    {
        $variationIds = [];
        $plentyId = Utils::getPlentyId();

        /** @var Query $query */
        $query = $this->db->query(ItemWishList::NAMESPACE);

        $contactId = $this->contactRepository->getContactId();

        if ($contactId > 0) {
            $rows = $query->where('contactId', '=', $contactId)->where('plentyId', '=', $plentyId)->get();

            /** @var ItemWishList $wishListModel */
            foreach ($rows as $wishListModel) {
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
        $contactId = $this->contactRepository->getContactId();
        $plentyId = Utils::getPlentyId();

        if ($variationId > 0) {
            $wishListEntry = $this->db->query(ItemWishList::NAMESPACE)->where('contactId', '=', $contactId)->where(
                'variationId',
                '=',
                $variationId
            )->where('plentyId', '=', $plentyId)->get();
        } else {
            throw new \Exception('ItemWishListRepository::isItemInWishList - variationId undefined', 400);
        }

        if (!count($wishListEntry)) {
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
        $contactId = $this->contactRepository->getContactId();
        $plentyId = Utils::getPlentyId();

        if ($contactId > 0 && $variationId > 0) {
            $wishListModels = $this->db->query(ItemWishList::NAMESPACE)->where('contactId', '=', $contactId)->where(
                'variationId',
                '=',
                $variationId
            )->where('plentyId', '=', $plentyId)->get();

            if (empty($wishListModels)) {
                $wishListEntry = pluginApp(ItemWishList::class);
                $wishListEntry->contactId = $contactId;
                $wishListEntry->variationId = $variationId;
                $wishListEntry->plentyId = $plentyId;
                $wishListEntry->quantity = $quantity;
                $wishListEntry->createdAt = date("Y-m-d H:i:s");

                $createdWishListEntry = $this->db->save($wishListEntry);


                $newEntry = pluginApp(ItemWishList::class);
                $newEntry->fillByAttributes(json_decode(json_encode($createdWishListEntry), true));

                return $newEntry;
            } else {
                $newEntry = pluginApp(ItemWishList::class);
                $newEntry->fillByAttributes(json_decode(json_encode($wishListModels[0]), true));

                return $newEntry;
            }
        } else {
            throw new \Exception(
                'ItemWishListRepository::addItemWishListEntry - user not logged in or variationId undefined', 401
            );
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
        $contactId = $this->contactRepository->getContactId();
        $plentyId = Utils::getPlentyId();

        if ($contactId > 0 && $variationId > 0) {
            $wishListModels = $this->db->query(ItemWishList::NAMESPACE)->where('contactId', '=', $contactId)->where(
                'variationId',
                '=',
                $variationId
            )->where('plentyId', '=', $plentyId)->get();

            foreach ($wishListModels as $wishListModel) {
                $response = $this->db->delete($wishListModel);
            }

            return $response;
        } else {
            throw new \Exception(
                'ItemWishListRepository::removeItemWishListEntry - user not logged in or variationId undefined', 401
            );
        }
    }
}
