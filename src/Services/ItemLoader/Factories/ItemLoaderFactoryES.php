<?php
namespace IO\Services\ItemLoader\Factories;

use IO\Extensions\Filters\NumberFormatFilter;
use IO\Services\CheckoutService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderSortingContract;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\ItemWishListService;
use IO\Services\SalesPriceService;
use IO\Services\SessionStorageService;
use IO\Services\CustomerService;
use IO\Services\UrlBuilder\VariationUrlBuilder;
use IO\Services\UrlService;
use Plenty\Legacy\Services\Item\Variation\SalesPriceService as BasePriceService;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchSearchRepositoryContract;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchMultiSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Authorization\Services\AuthHelper;

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

        $result = $this->attachPrices($result, $options);
        $result = $this->attachItemWishList($result);
        $result = $this->attachURLs($result);

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
                        $search->addFilter($filter);
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
                $result[$identifiers[$key-1]] = $this->attachPrices($list);
                $list = $result[$identifiers[$key-1]];
                $result[$identifiers[$key-1]] = $this->attachItemWishList($list);

            }
        }
    
        foreach ($this->facetExtensionContainer->getFacetExtensions() as $facetExtension) {
            $result = $facetExtension->mergeIntoFacetsList($result);
        }

        return $result;
    }

    private function attachPrices($result, $options = [])
    {
        if(count($result['documents']))
        {
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);

            /** @var CheckoutService $checkoutService */
            $checkoutService = pluginApp(CheckoutService::class);

            $customerClassMinimumOrderQuantity = $customerService->getContactClassMinimumOrderQuantity();
            
            /**
             * @var SalesPriceService $salesPriceService
             */
            $salesPriceService = pluginApp(SalesPriceService::class);
            $salesPriceService->setClassId( $customerService->getContactClassId() );
            $salesPriceService->setCurrency( $checkoutService->getCurrency() );

            foreach($result['documents'] as $key => $variation)
            {
                if((int)$variation['data']['variation']['id'] > 0)
                {
                    if((int)$customerClassMinimumOrderQuantity > $variation['data']['variation']['minimumOrderQuantity'])
                    {
                        $variation['data']['variation']['minimumOrderQuantity'] = $customerClassMinimumOrderQuantity;
                    }
                    
                    $quantity = 1;
                    if(isset($options['basketVariationQuantities'][$variation['data']['variation']['id']]) && (int)$options['basketVariationQuantities'][$variation['data']['variation']['id']] > 0)
                    {
                        $quantity = (int)$options['basketVariationQuantities'][$variation['data']['variation']['id']];
                    }

                    $numberFormatFilter = pluginApp(NumberFormatFilter::class);

                    $salesPrice = $salesPriceService->getSalesPriceForVariation($variation['data']['variation']['id'], 'default', $quantity);

                    $graduated = [];

                    if(count($variation['data']['salesPrices']) > 1)
                    {
                        $graduated = $salesPriceService->getAllSalesPricesForVariation($variation['data']['variation']['id'], 'default');
                    }

                    $graduatedPrices = [];

                    if(is_array($graduated) && count($graduated))
                    {
                        $graduatedMinQuantities = array();
                        foreach($graduated as $gpKey => $gp)
                        {
                            if ($gp instanceof SalesPriceSearchResponse)
                            {
                                // check if graduated price for current minimum order quantity has already been added.
                                // => priority of prices with same minimum order quantity is based on the position of the price defined by user
                                if ( !in_array( $gp->minimumOrderQuantity, $graduatedMinQuantities ) )
                                {
                                    $graduatedMinQuantities[] = $gp->minimumOrderQuantity;
                                    $graduatedPrices[] = $gp;
                                }
                            }
                        }

                        /*
                        foreach($graduated as $gpKey => $gp)
                        {
                            if($gp instanceof SalesPriceSearchResponse)
                            {
                                if($gp->salesPriceId == $salesPrice->salesPriceId)
                                {
                                    unset($graduated[$gpKey]);
                                }
                            }
                        }
                        */

                        //$graduatedPrices = $graduated;
                    }

                    if($salesPrice instanceof SalesPriceSearchResponse)
                    {
                        $variation['data']['calculatedPrices']['default'] = $salesPrice;
                        $variation['data']['calculatedPrices']['formatted']['defaultPrice'] = $numberFormatFilter->formatMonetary($salesPrice->price, $salesPrice->currency);
                        $variation['data']['calculatedPrices']['formatted']['defaultUnitPrice'] = $numberFormatFilter->formatMonetary($salesPrice->unitPrice, $salesPrice->currency);

                        $variation['data']['calculatedPrices']['graduatedPrices'] = [];
                        if(count($graduatedPrices))
                        {
                            foreach($graduatedPrices as $graduatedPrice)
                            {
                                if($graduatedPrice instanceof SalesPriceSearchResponse)
                                {
                                    $variation['data']['calculatedPrices']['graduatedPrices'][] = [
                                        'minimumOrderQuantity' => (int)$graduatedPrice->minimumOrderQuantity,
                                        'price'                => (float)$graduatedPrice->unitPrice,
                                        'formatted'            => $numberFormatFilter->formatMonetary($graduatedPrice->unitPrice, $graduatedPrice->currency)
                                    ];
                                }
                            }
                        }

                        /**
                         * @var BasePriceService $basePriceService
                         */
                        $basePriceService = pluginApp(BasePriceService::class);

                        $lot = $variation['data']['unit']['content'];
                        $unit = $variation['data']['unit']['unitOfMeasurement'];

                        $basePriceString = '';
                        if($variation['data']['variation']['mayShowUnitPrice'] == true && $lot > 0 && strlen($unit))
                        {
                            $basePrice = [];
                            list($basePrice['lot'], $basePrice['price'], $basePrice['unitKey']) = $basePriceService->getUnitPrice($lot, $salesPrice->unitPrice, $unit);

                            /**
                             * @var UnitRepositoryContract $unitRepository
                             */
                            $unitRepository = pluginApp(UnitRepositoryContract::class);

                            /** @var AuthHelper $authHelper */
                            $authHelper = pluginApp(AuthHelper::class);

                            $unitData = $authHelper->processUnguarded( function() use ($unitRepository, $basePrice)
                            {
                                $unitRepository->setFilters(['unitOfMeasurement' => $basePrice['unitKey']]);
                                return $unitRepository->all(['*'], 1, 1);
                            });


                            $unitId = $unitData->getResult()->first()->id;

                            /**
                             * @var UnitNameRepositoryContract $unitNameRepository
                             */
                            $unitNameRepository = pluginApp(UnitNameRepositoryContract::class);
                            $unitName = $unitNameRepository->findOne($unitId, pluginApp(SessionStorageService::class)->getLang())->name;

                            $basePriceString = $numberFormatFilter->formatMonetary($basePrice['price'], $salesPrice->currency).' / '.($basePrice['lot'] > 1 ? $basePrice['lot'].' ' : '').$unitName;
                        }

                        $variation['data']['calculatedPrices']['formatted']['basePrice'] = $basePriceString;
                    }


                    $rrp = $salesPriceService->getSalesPriceForVariation($variation['data']['variation']['id'], 'rrp');
                    if($rrp instanceof SalesPriceSearchResponse)
                    {
                        $variation['data']['calculatedPrices']['rrp'] = $rrp;
                        $variation['data']['calculatedPrices']['formatted']['rrpPrice'] = $numberFormatFilter->formatMonetary($rrp->price, $rrp->currency);
                        $variation['data']['calculatedPrices']['formatted']['rrpUnitPrice'] = $numberFormatFilter->formatMonetary($rrp->unitPrice, $rrp->currency);
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
}