<?php

namespace IO\Services;

use IO\Constants\CrossSellingType;
use IO\Constants\SessionStorageKeys;
use IO\Services\SessionStorageService;

/**
 * Class ItemCrossSellingService
 * @package IO\Services
 */
class ItemCrossSellingService
{
    private $sessionStorage;
    
    /**
     * ItemLastSeenService constructor.
     * @param \IO\Services\SessionStorageService $sessionStorage
     */
    public function __construct(SessionStorageService $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }
    
    /**
     * @param string $type
     */
    public function setType($type)
    {
        if(strlen($type))
        {
            $this->sessionStorage->setSessionValue(SessionStorageKeys::CROSS_SELLING_TYPE, $type);
        }
    }
    
    public function getType()
    {
        $type = $this->sessionStorage->getSessionValue(SessionStorageKeys::CROSS_SELLING_TYPE);
        
        if(!is_null($type) && strlen($type))
        {
            return $type;
        }
        
        return CrossSellingType::SIMILAR;
    }
}