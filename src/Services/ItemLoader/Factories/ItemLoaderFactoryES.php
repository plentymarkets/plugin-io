<?php
namespace IO\Services\ItemLoader\Factories;

use IO\Extensions\Filters\NumberFormatFilter;
use IO\Helper\DefaultSearchResult;
use IO\Services\CheckoutService;
use IO\Helper\VariationPriceList;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderSortingContract;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\ItemWishListService;
use IO\Services\CustomerService;
use IO\Services\UrlBuilder\VariationUrlBuilder;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchSearchRepositoryContract;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchMultiSearchRepositoryContract;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Plugin\ConfigRepository;

/**
 * Created by ptopczewski, 09.01.17 08:35
 * Class ItemLoaderFactoryES
 * @package IO\Services\ItemLoader\Factories
 */
class ItemLoaderFactoryES implements ItemLoaderFactory
{
    /**
     * @var FacetExtensionContainer
     */
    private $facetExtensionContainer;

    /**
     * ItemLoaderFactoryES constructor.
     * @param FacetExtensionContainer $facetExtensionContainer
     */
    public function __construct(FacetExtensionContainer $facetExtensionContainer)
    {
        $this->facetExtensionContainer = $facetExtensionContainer;
    }

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

        $result = $this->normalizeResult($result);
        $result = $this->attachPrices($result, $options);
        $result = $this->attachItemWishList($result);
        $result = $this->attachURLs($result);
        if ( array_key_exists('facets', $result ) )
        {
            $result['facets'] = $this->filterFacets( $result['facets'] );
        }

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
                $options = $loader->setOptions($options);
                if(!$search instanceof DocumentSearch)
                {
                    //search, filter
                    $search = $loader->getSearch();
                }

                foreach($loader->getFilterStack($options) as $filter)
                {
                    if($filter instanceof SearchFilter)
                    {
                        $search->addQuery($filter);
                    }
                    else
                    {
                        $search->addFilter($filter);
                    }
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
            else
            {
                $currentFields = $loader->getResultFields( $resultFields );
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
        foreach ($this->facetExtensionContainer->getFacetExtensions() as $facetExtension) {
            $result = $facetExtension->mergeIntoFacetsList($result);
        }

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
        
        $options['loaderClassList'] = $loaderClassList;

        foreach($loaderClassList as $type => $loaderClasses)
        {
            foreach($loaderClasses as $identifier => $loaderClass)
            {
                /** @var ItemLoaderContract $loader */
                $loader = pluginApp($loaderClass);

                if($loader instanceof ItemLoaderContract)
                {
                    $options = $loader->setOptions($options);
                    if(!$search instanceof DocumentSearch)
                    {
                        $search = $loader->getSearch();
                    }

                    foreach($loader->getFilterStack($options) as $filter)
                    {
                        if($filter instanceof SearchFilter)
                        {
                            $search->addQuery($filter);
                        }
                        else
                        {
                            $search->addFilter($filter);
                        }
                    }
                }

                //sorting
                if($loader instanceof ItemLoaderSortingContract)
                {
                    /** @var ItemLoaderSortingContract $loader */
                    $sorting = $loader->getSorting($options);
                    if($sorting instanceof SortingInterface && $sorting !== null )
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
                else
                {
                    $currentFields = $loader->getResultFields( $resultFields );
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
    
                if(!is_null($search))
                {
                    $elasticSearchRepo->addSearch($search);
                    $search = null;
                }
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
                $identifier = $identifiers[$key-1];
                $result[$identifier] = $this->attachPrices($list);
                if ( $identifier !== "Facets" )
                {
                    $result[$identifier] = $this->normalizeResult( $result[$identifier] );
                }
                $result[$identifier] = $this->attachItemWishList( $result[$identifier] );

            }
        }
    
        foreach ($this->facetExtensionContainer->getFacetExtensions() as $facetExtension) {
            $result = $facetExtension->mergeIntoFacetsList($result);
        }

        return $result;
    }

    private function attachPrices($result, $options = [])
    {
        if(count($result['documents'])) {
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);
            $customerClassMinimumOrderQuantity = $customerService->getContactClassMinimumOrderQuantity();

            foreach ( $result['documents'] as $key => $variation )
            {
                if ( (int)$variation['data']['variation']['id'] > 0 )
                {
                    $variationId        = $variation['data']['variation']['id'];
                    $minimumQuantity    = $variation['data']['variation']['minimumOrderQuantity'];
                    if ( $minimumQuantity === null )
                    {
                        // mimimum order quantity is not defined => get smallest possible quantity depending on interval order quantity
                        if ( $variation['data']['variation']['intervalOrderQuantity'] !== null )
                        {
                            $minimumQuantity = $variation['data']['variation']['intervalOrderQuantity'];
                        }
                        else
                        {
                            // no interval quantity defined => minimum order quantity should be 1
                            $minimumQuantity = 1;
                        }
                    }

                    if ( (float)$customerClassMinimumOrderQuantity > $minimumQuantity )
                    {
                        // minimum order quantity is overridden by contact class
                        $minimumQuantity = $customerClassMinimumOrderQuantity;
                    }

                    // assign generated minimum quantity
                    $variation['data']['variation']['minimumOrderQuantity'] = $minimumQuantity;

                    if ( $variation['data']['variation']['maximumOrderQuantity'] <= 0 )
                    {
                        // remove invalid maximum order quantity
                        $variation['data']['variation']['maximumOrderQuantity'] = null;
                    }
                    $maximumOrderQuantity = $variation['data']['variation']['maximumOrderQuantity'];

                    $lot = 0;
                    $unit = null;
                    if ( $variation['data']['variation']['mayShowUnitPrice'] )
                    {
                        $lot = $variation['data']['unit']['content'];
                        $unit = $variation['data']['unit']['unitOfMeasurement'];
                    }


                    $priceList = VariationPriceList::create( $variationId, $minimumQuantity, $maximumOrderQuantity, $lot, $unit );

                    // assign minimum order quantity from price list (may be recalculated depending on available graduated prices)
                    $variation['data']['variation']['minimumOrderQuantity'] = $priceList->minimumOrderQuantity;


                    $quantity = $priceList->minimumOrderQuantity;
                    if ( isset($options['basketVariationQuantities'][$variationId])
                        && (float)$options['basketVariationQuantities'][$variationId] > 0 )
                    {
                        // override quantity by options
                        $quantity = (float)$options['basketVariationQuantities'][$variationId];
                    }

                    $variation['data']['calculatedPrices'] = $priceList->getCalculatedPrices( $quantity );
                    $variation['data']['prices'] = $priceList->toArray( $quantity );


                    $result['documents'][$key] = $variation;
                }
            }
        }

        return $result;
    }


    private function attachItemWishList($result)
    {
        /**
         * @var ConfigRepository $configRepo
         */
        $configRepo = pluginApp(ConfigRepository::class);
        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );

        if(in_array('wish-list', $enabledRoutes) && count($result['documents']))
        {
            /**
             * @var ItemWishListService $itemWishListService
             */
            $itemWishListService = pluginApp(ItemWishListService::class);

            foreach($result['documents'] as $key => $variation)
            {
                if((int)$variation['data']['variation']['id'] > 0)
                {
                    $result['documents'][$key]["isInWishListVariation"] = $itemWishListService->isItemInWishList((int)$variation['data']['variation']['id']);
                }
            }
        }
        return $result;
    }

    private function attachURLs($result)
    {
        if ( count( $result ) && count( $result['ItemURLs'] ) )
        {
            /** @var VariationUrlBuilder $itemUrlBuilder */
            $itemUrlBuilder = pluginApp( VariationUrlBuilder::class );
            $itemUrlDocuments = $result['ItemURLs']['documents'];
            foreach( $itemUrlDocuments as $key => $urlDocument )
            {
                VariationUrlBuilder::fillItemUrl( $urlDocument['data'] );
                $document = $result['documents'][$key];
                if ( count( $document )
                    && count( $document['data']['texts'] )
                    && strlen( $document['data']['texts']['urlPath'] ) <= 0 )
                {
                    // attach generated item url if not defined
                    $itemUrl = $itemUrlBuilder->buildUrl(
                        $urlDocument['data']['item']['id'],
                        $urlDocument['data']['variation']['id']
                    )->getPath();
                    $result['documents'][$key]['data']['texts']['urlPath'] = $itemUrl;
                }

            }
        }

        return $result;
    }

    private function normalizeResult($result)
    {
        if( count($result['documents']) )
        {
            foreach($result['documents'] as $key => $variation)
            {
                $result['documents'][$key]['data'] = DefaultSearchResult::merge( $variation['data'] );
            }
        }

        return $result;
    }

    private function filterFacets($facets)
    {
        $filteredFacets = [];

        foreach( $facets as $facet )
        {
            if ( (int) $facet['count'] >= (int) $facet['minHitCount'] )
            {

                $facet['values'] = array_slice( $facet['values'], 0, (int) $facet['maxResultCount'] );
                $filteredFacets[] = $facet;
            }
        }
        return $filteredFacets;
    }
}