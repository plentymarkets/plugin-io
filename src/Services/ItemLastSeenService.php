<?php

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use Plenty\Plugin\CachingRepository;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

/**
 * Class ItemLastSeenService
 * @package IO\Services
 */
class ItemLastSeenService
{
    const MAX_COUNT = 9;
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
     * @param int $maxCount
     */
    public function setLastSeenMaxCount(int $maxCount)
    {
        $this->cachingRepository->put(SessionStorageKeys::LAST_SEEN_MAX_COUNT . '_' . $this->basketRepository->load()->id, $maxCount, 60);
    }

    /**
     * @param int $variationId
     */
    public function setLastSeenItem(int $variationId)
    {
        $maxCount = $this->cachingRepository->get(SessionStorageKeys::LAST_SEEN_MAX_COUNT . '_' . $this->basketRepository->load()->id);
        if(is_null($maxCount))
        {
            $maxCount = self::MAX_COUNT;
        }

        $lastSeenItems = $this->cachingRepository->get(SessionStorageKeys::LAST_SEEN_ITEMS . '_' . $this->basketRepository->load()->id);

        if(is_null($lastSeenItems))
        {
            $lastSeenItems = [];
        }

        if(!in_array($variationId, $lastSeenItems))
        {
            if(count($lastSeenItems) >= $maxCount)
            {
                array_pop($lastSeenItems);
            }

            array_unshift($lastSeenItems, $variationId);
            $this->cachingRepository->put(SessionStorageKeys::LAST_SEEN_ITEMS . '_' . $this->basketRepository->load()->id, $lastSeenItems,60);
        }
    }
}