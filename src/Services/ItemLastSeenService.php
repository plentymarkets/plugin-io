<?php

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use Plenty\Plugin\CachingRepository;

/**
 * Class ItemLastSeenService
 * @package IO\Services
 */
class ItemLastSeenService
{
    const MAX_COUNT = 9;
    private $cachingRepository;
    
    /**
     * ItemLastSeenService constructor.
     * @param CachingRepository $cachingRepository
     */
    public function __construct(CachingRepository $cachingRepository)
    {
        $this->cachingRepository = $cachingRepository;
    }
    
    /**
     * @param int $maxCount
     */
    public function setLastSeenMaxCount(int $maxCount)
    {
        $this->cachingRepository->put(SessionStorageKeys::LAST_SEEN_MAX_COUNT, $maxCount, 60);
    }
    
    /**
     * @param int $variationId
     */
    public function setLastSeenItem(int $variationId)
    {
        $maxCount = $this->cachingRepository->get(SessionStorageKeys::LAST_SEEN_MAX_COUNT);
        if(is_null($maxCount))
        {
            $maxCount = self::MAX_COUNT;
        }

        $lastSeenItems = $this->cachingRepository->get(SessionStorageKeys::LAST_SEEN_ITEMS);
    
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
            $this->cachingRepository->put(SessionStorageKeys::LAST_SEEN_ITEMS, $lastSeenItems,60);
        }
    }
}