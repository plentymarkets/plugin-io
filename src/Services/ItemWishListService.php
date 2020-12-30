<?php

namespace IO\Services;

use IO\Repositories\ItemWishListRepository;
use IO\Repositories\ItemWishListGuestRepository;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;

/**
 * Service Class WishListService
 *
 * This service class contains functions related to the customers wish list.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ItemWishListService
{
    /**
     * @var mixed
     */
    private $itemWishListRepo;

    /**
     * ItemWishListService constructor.
     * @param SessionStorageRepositoryContract $sessionStorageRepositoryContract
     */
    public function __construct(SessionStorageRepositoryContract $sessionStorageRepositoryContract)
    {
        if ($sessionStorageRepositoryContract->getSessionValue(
            SessionStorageRepositoryContract::GUEST_WISHLIST_MIGRATION
        )) {
            $this->migrateGuestItemWishList();
            $sessionStorageRepositoryContract->setSessionValue(
                SessionStorageRepositoryContract::GUEST_WISHLIST_MIGRATION,
                false
            );
        }

        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        if ((int)$contactRepository->getContactId() > 0) {
            $itemWishListRepo = pluginApp(ItemWishListRepository::class);
        } else {
            $itemWishListRepo = pluginApp(ItemWishListGuestRepository::class);
        }

        $this->itemWishListRepo = $itemWishListRepo;
    }

    /**
     * Add a variation to the wish list
     * @param int $variationId An variation id
     * @param int $quantity The desired quantity of the variation
     * @return mixed
     */
    public function addItemWishListEntry(int $variationId, int $quantity)
    {
        return $this->itemWishListRepo->addItemWishListEntry($variationId, $quantity);
    }

    /**
     * Check if a variation is in the wish list
     * @param int $variationId An variation id
     * @return bool
     */
    public function isItemInWishList(int $variationId)
    {
        return $this->itemWishListRepo->isItemInWishList($variationId);
    }

    /**
     * Get a list of all variation ids in the wish list
     * @return array
     */
    public function getItemWishList()
    {
        return $this->itemWishListRepo->getItemWishList();
    }

    /**
     * Get number of entries in wish list
     * @return int
     */
    public function getCountedItemWishList()
    {
        return $this->itemWishListRepo->getCountedItemWishList();
    }

    /**
     * Remove a variation from the wish list
     * @param int $variationId An variation id
     * @return bool
     */
    public function removeItemWishListEntry(int $variationId)
    {
        return $this->itemWishListRepo->removeItemWishListEntry($variationId);
    }

    /**
     * Migrates a guest wish list into a contacts wish list.
     * @throws \Exception
     */
    public function migrateGuestItemWishList()
    {
        /**
         * @var ItemWishListGuestRepository $guestWishListRepo
         */
        $guestWishListRepo = pluginApp(ItemWishListGuestRepository::class);

        $guestWishList = $guestWishListRepo->getItemWishList();

        if (count($guestWishList)) {
            /**
             * @var ItemWishListRepository $contactWishListRepo
             */
            $contactWishListRepo = pluginApp(ItemWishListRepository::class);

            foreach ($guestWishList as $variationId) {
                if ((int)$variationId > 0) {
                    $contactWishListRepo->addItemWishListEntry($variationId);
                }
            }

            $guestWishListRepo->resetItemWishList();
        }
    }
}
