<?php

namespace IO\Services;

use IO\Constants\CrossSellingType;
use IO\Constants\SessionStorageKeys;
use IO\Services\ItemSearch\Helper\SortingHelper;
use IO\Services\SessionStorageService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;

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
    
    public function setSorting($sorting)
    {
        if(!strlen($sorting))
        {
            $sorting = 'texts.'.SortingHelper::getUsedItemName().'_'.ElasticSearch::SORTING_ORDER_ASC;
        }

        $this->sessionStorage->setSessionValue(SessionStorageKeys::CROSS_SELLING_SORTING, $sorting);
    }
    
    public function getSorting()
    {
        $sorting = $this->sessionStorage->getSessionValue(SessionStorageKeys::CROSS_SELLING_SORTING);
        
        if(is_null($sorting) || !strlen($sorting))
        {
            $sorting = 'texts.'.SortingHelper::getUsedItemName().'_'.ElasticSearch::SORTING_ORDER_ASC;
        }
        
        return $sorting;
    }
}