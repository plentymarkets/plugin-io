<?php

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use Plenty\Modules\Webshop\ItemSearch\Helper\SortingHelper;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;

/**
 * Class ItemCrossSellingService
 * @package IO\Services
 */
class ItemCrossSellingService
{
    private $sessionStorage;
    
    /** @var TemplateConfigRepositoryContract */
    private $templateConfigRepo;
    
    /** @var SortingHelper */
    private $sortingHelper;
    
    /**
     * ItemLastSeenService constructor.
     * @param SessionStorageService $sessionStorage
     * @param TemplateConfigRepositoryContract $templateConfigRepo
     * @param SortingHelper $sortingHelper
     */
    public function __construct(SessionStorageService $sessionStorage, TemplateConfigRepositoryContract $templateConfigRepo, SortingHelper $sortingHelper)
    {
        $this->sessionStorage = $sessionStorage;
        $this->templateConfigRepo = $templateConfigRepo;
        $this->sortingHelper = $sortingHelper;
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
        return $this->templateConfigRepo->get('item.lists.cross_selling_type', 'Similar');
    }
    
    public function setSorting($sorting)
    {
        if(!strlen($sorting))
        {
            $sorting = 'texts.'.$this->sortingHelper->getUsedItemName().'_'.ElasticSearch::SORTING_ORDER_ASC;
        }

        $this->sessionStorage->setSessionValue(SessionStorageKeys::CROSS_SELLING_SORTING, $sorting);
    }
    
    public function getSorting()
    {
        $sorting = $this->templateConfigRepo->get('item.lists.cross_selling_sorting');
        
        if(is_null($sorting) || !strlen($sorting))
        {
            $sorting = 'texts.'.$this->sortingHelper->getUsedItemName().'_'.ElasticSearch::SORTING_ORDER_ASC;
        }
        
        return $sorting;
    }
}
