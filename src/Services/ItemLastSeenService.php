<?php

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use IO\Services\SessionStorageService;

class ItemLastSeenService
{
    const MAX_LENGTH = 10;
    
    public function __construct()
    {
    
    }
    
    public function setLastSeenItem(int $variationId)
    {
        /**
         * @var SessionStorageService $sessionStorage
         */
        $sessionStorage = pluginApp(SessionStorageService::class);
        $lastSeenItems = $sessionStorage->getSessionValue(SessionStorageKeys::LAST_SEEN_ITEMS);
    
        if(is_null($lastSeenItems))
        {
            $lastSeenItems = [];
        }
        
        if(!in_array($variationId, $lastSeenItems))
        {
            if(count($lastSeenItems) >= self::MAX_LENGTH)
            {
                array_pop($lastSeenItems);
            }
            
            array_unshift($lastSeenItems, $variationId);
            $sessionStorage->setSessionValue(SessionStorageKeys::LAST_SEEN_ITEMS, $lastSeenItems);
        }
    }
}