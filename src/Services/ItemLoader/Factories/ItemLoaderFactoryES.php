<?php
namespace IO\Services\ItemLoader\Factories;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderSortingContract;
use IO\Services\SalesPriceService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchSearchRepositoryContract;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchMultiSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;

/**
 * Created by ptopczewski, 09.01.17 08:35
 * Class ItemLoaderFactoryES
 * @package IO\Services\ItemLoader\Factories
 */
class ItemLoaderFactoryES implements ItemLoaderFactory
{
    /**
     * @param array $loaderClassList
     * @param array $resultFields
     * @param array $options
     * @return array
     */
    public function runSearch($loaderClassList, $resultFields,  $options = [])
    {
        $result = [];
        
        $isMultiSearch = false;
        if(isset($loaderClassList['multi']) && count($loaderClassList['multi']))
        {
            $isMultiSearch = true;
        }
        elseif(!isset($loaderClassList['single']))
        {
            $classList['single'] = $loaderClassList;
            $loaderClassList = $classList;
        }
        
        if($isMultiSearch)
        {
            $result = $this->buildMultiSearch($loaderClassList, $resultFields, $options);
        }
        else
        {
            $result = $this->buildSingleSearch($loaderClassList['single'], $resultFields, $options);
        }
        
        $result = $this->attachPrices($result, $options);
        
        return $result;
    }
    
    private function buildSingleSearch($loaderClassList, $resultFields, $options = [])
    {
        /** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
        $elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
        
        $search = null;
        
        foreach($loaderClassList as $loaderClass)
        {
            /** @var ItemLoaderContract $loader */
            $loader = pluginApp($loaderClass);
            
            if($loader instanceof ItemLoaderContract)
            {
                if(!$search instanceof DocumentSearch)
                {
                    //search, filter
                    $search = $loader->getSearch();
                }
                
                foreach($loader->getFilterStack($options) as $filter)
                {
                    $search->addFilter($filter);
                }
            }
            
            //sorting
            if($loader instanceof ItemLoaderSortingContract)
            {
                /** @var ItemLoaderSortingContract $loader */
                $sorting = $loader->getSorting($options);
                if($sorting instanceof SortingInterface)
                {
                    $search->setSorting($sorting);
                }
            }
            
            if($loader instanceof ItemLoaderPaginationContract)
            {
                if($search instanceof DocumentSearch)
                {
                    /** @var ItemLoaderPaginationContract $loader */
                    $search->setPage($loader->getCurrentPage($options), $loader->getItemsPerPage($options));
                }
            }
            
            /** @var IncludeSource $source */
            $source = pluginApp(IncludeSource::class);
            
            $currentFields = $resultFields;
            if(array_key_exists($loaderClass, $currentFields))
            {
                $currentFields = $currentFields[$loaderClass];
            }
            
            $fieldsFound = false;
            foreach($currentFields as $fieldName)
            {
                $source->activateList([$fieldName]);
                $fieldsFound = true;
            }
            
            if(!$fieldsFound)
            {
                $source->activateAll();
            }
            
            $search->addSource($source);
            
            $aggregations = $loader->getAggregations();
            if(count($aggregations))
            {
                foreach($aggregations as $aggregation)
                {
                    $search->addAggregation($aggregation);
                }
            }
        }
        
        if(!is_null($search))
        {
            $elasticSearchRepo->addSearch($search);
        }
        
        $result = $elasticSearchRepo->execute();
        
        return $result;
    }
    
    private function buildMultiSearch($loaderClassList, $resultFields, $options = [])
    {
        /**
         * @var VariationElasticSearchMultiSearchRepositoryContract $elasticSearchRepo
         */
        $elasticSearchRepo = pluginApp(VariationElasticSearchMultiSearchRepositoryContract::class);
        
        $search = null;
        
        $identifiers = [];
        
        foreach($loaderClassList as $type => $loaderClasses)
        {
            foreach($loaderClasses as $identifier => $loaderClass)
            {
                /** @var ItemLoaderContract $loader */
                $loader = pluginApp($loaderClass);
                
                if($loader instanceof ItemLoaderContract)
                {
                    if(!$search instanceof DocumentSearch)
                    {
                        //search, filter
                        $search = $loader->getSearch();
                    }
                    
                    foreach($loader->getFilterStack($options) as $filter)
                    {
                        $search->addFilter($filter);
                    }
                }
                
                //sorting
                if($loader instanceof ItemLoaderSortingContract)
                {
                    /** @var ItemLoaderSortingContract $loader */
                    $sorting = $loader->getSorting($options);
                    if($sorting instanceof SortingInterface)
                    {
                        $search->setSorting($sorting);
                    }
                }
                
                if($loader instanceof ItemLoaderPaginationContract)
                {
                    if($search instanceof DocumentSearch)
                    {
                        /** @var ItemLoaderPaginationContract $loader */
                        $search->setPage($loader->getCurrentPage($options), $loader->getItemsPerPage($options));
                    }
                }
                
                /** @var IncludeSource $source */
                $source = pluginApp(IncludeSource::class);
                
                $currentFields = $resultFields;
                if(array_key_exists($loaderClass, $currentFields))
                {
                    $currentFields = $currentFields[$loaderClass];
                }
                
                $fieldsFound = false;
                foreach($currentFields as $fieldName)
                {
                    $source->activateList([$fieldName]);
                    $fieldsFound = true;
                }
                
                if(!$fieldsFound)
                {
                    $source->activateAll();
                }
                
                $search->addSource($source);
                
                $aggregations = $loader->getAggregations();
                if(count($aggregations))
                {
                    foreach($aggregations as $aggregation)
                    {
                        $search->addAggregation($aggregation);
                    }
                }
                
                if($type == 'multi')
                {
                    $e = explode('\\', $loaderClass);
                    $identifier = $e[count($e)-1];
                    if(!in_array($identifier, $identifiers))
                    {
                        $identifiers[] = $identifier;
                    }
                }
            }
            
            if(!is_null($search))
            {
                $elasticSearchRepo->addSearch($search);
                $search = null;
            }
        }
        
        $rawResult = $elasticSearchRepo->execute();
        
        $result = [];
        foreach($rawResult as $key => $list)
        {
            if($key == 0)
            {
                foreach($list as $k => $entry)
                {
                    $result[$k] = $entry;
                }
            }
            else
            {
                $result[$identifiers[$key-1]] = $list;
            }
        }
        
        return $result;
    }
    
    private function attachPrices($result, $options = [])
    {
        if(count($result['documents']))
        {
            /**
             * @var SalesPriceService $salesPriceService
             */
            $salesPriceService = pluginApp(SalesPriceService::class);
            
            foreach($result['documents'] as $key => $variation)
            {
                if((int)$variation['data']['variation']['id'] > 0)
                {
                    $quantity = 1;
                    if(isset($options['basketVariationQuantities'][$variation['data']['variation']['id']]) && (int)$options['basketVariationQuantities'][$variation['data']['variation']['id']] > 0)
                    {
                        $quantity = (int)$options['basketVariationQuantities'][$variation['data']['variation']['id']];
                    }
                    
                    $salesPrice = $salesPriceService->getSalesPriceForVariation($variation['data']['variation']['id'], 'default', $quantity);
                    if($salesPrice instanceof SalesPriceSearchResponse)
                    {
                        $variation['data']['calculatedPrices']['default'] = $salesPrice;
                    }
                    
                    $rrp = $salesPriceService->getSalesPriceForVariation($variation['data']['variation']['id'], 'rrp');
                    if($rrp instanceof SalesPriceSearchResponse)
                    {
                        $variation['data']['calculatedPrices']['rrp'] = $rrp;
                    }
                    
                    $specialOffer = $salesPriceService->getSalesPriceForVariation($variation['data']['variation']['id'], 'specialOffer');
                    if($specialOffer instanceof SalesPriceSearchResponse)
                    {
                        $variation['data']['calculatedPrices']['specialOffer'] = $specialOffer;
                    }
                    
                    $result['documents'][$key] = $variation;
                }
            }
        }
        
        return $result;
    }
}