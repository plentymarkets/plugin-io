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
    
    /** @var TemplateConfigService */
    private $templateConfigService;
    
    /**
     * ItemLastSeenService constructor.
     * @param SessionStorageService $sessionStorage
     * @param TemplateConfigService $templateConfigService
     */
    public function __construct(SessionStorageService $sessionStorage, TemplateConfigService $templateConfigService)
    {
        $this->sessionStorage = $sessionStorage;
        $this->templateConfigService = $templateConfigService;
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
        return $this->templateConfigService->get('item.lists.cross_selling_type', 'Similar');
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
        $sorting = $this->templateConfigService->get('item.lists.cross_selling_sorting');
        
        if(is_null($sorting) || !strlen($sorting))
        {
            $sorting = 'texts.'.SortingHelper::getUsedItemName().'_'.ElasticSearch::SORTING_ORDER_ASC;
        }
        
        return $sorting;
    }
}