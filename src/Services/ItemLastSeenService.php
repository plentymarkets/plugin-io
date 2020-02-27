<?php

namespace IO\Services;

use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\CachingRepository;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

/**
 * Class ItemLastSeenService
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
     * @param int $variationId
     */
    public function setLastSeenItem(int $variationId)
    {
        $lastSeenItems = $this->cachingRepository->get(SessionStorageRepositoryContract::LAST_SEEN_ITEMS . '_' . $this->basketRepository->load()->id);

        if(is_null($lastSeenItems))
        {
            $lastSeenItems = [];
        }

        if(!in_array($variationId, $lastSeenItems))
        {
            if(count($lastSeenItems) >= self::MAX_COUNT)
            {
                array_pop($lastSeenItems);
            }

            array_unshift($lastSeenItems, $variationId);
            $this->cachingRepository->put(SessionStorageRepositoryContract::LAST_SEEN_ITEMS . '_' . $this->basketRepository->load()->id, $lastSeenItems,60);
        }
    }
}
