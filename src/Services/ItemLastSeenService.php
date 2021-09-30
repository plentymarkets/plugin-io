<?php

namespace IO\Services;

use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\CachingRepository;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

/**
 * Class ItemLastSeenService
 *
 * This service class contains function related to the "last seen items" functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ItemLastSeenService
{
    const MAX_COUNT = 20;
    private $cachingRepository;
    private $basketRepository;

    /**
     * ItemLastSeenService constructor.
     * @param CachingRepository $cachingRepository
     */
    public function __construct(CachingRepository $cachingRepository, BasketRepositoryContract $basketRepository)
    {
        $this->cachingRepository = $cachingRepository;
        $this->basketRepository = $basketRepository;
    }

    /**
     * Adds a new item to the last seen cache
     * @param int $variationId Variation id of the item
     */
    public function setLastSeenItem(int $variationId)
    {
        $lastSeenItems = $this->cachingRepository->get(SessionStorageRepositoryContract::LAST_SEEN_ITEMS . '_' . $this->basketRepository->load()->sessionId);

        if (is_null($lastSeenItems)) {
            $lastSeenItems = [];
        }

        if (!in_array($variationId, $lastSeenItems)) {
            if (count($lastSeenItems) >= self::MAX_COUNT) {
                array_pop($lastSeenItems);
            }

            array_unshift($lastSeenItems, $variationId);
            $this->cachingRepository->put(SessionStorageRepositoryContract::LAST_SEEN_ITEMS . '_' . $this->basketRepository->load()->sessionId, $lastSeenItems, 60);
        }
    }
}
