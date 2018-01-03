<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Builder\Facet\FacetBuilder;
use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Helper\WebshopFilterBuilder;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\SessionStorageService;
use IO\Services\PriceDetectService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Aggregations\FacetAggregation;
use Plenty\Modules\Item\Search\Aggregations\FacetAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\FacetFilter;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Modules\Item\Search\Helper\SearchHelper;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItemAttributes
 * @package IO\Services\ItemLoader\Loaders
 */
class Facets implements ItemLoaderContract
{
    private $options = [];
    
    /**
     * @var FacetExtensionContainer
     */
    private $facetExtensionContainer;

    /**
     * Facets constructor.
     * @param FacetExtensionContainer $facetExtensionContainer
     */
    public function __construct(FacetExtensionContainer $facetExtensionContainer)
    {
        $this->facetExtensionContainer = $facetExtensionContainer;
    }

    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $plentyId = pluginApp(Application::class)->getPlentyId();
        $lang = pluginApp(SessionStorageService::class)->getLang();
    
        $filters = $this->getFacetValues($this->options);
        $facetValues = $filters['facetValues'];
        
        /** @var SearchHelper $searchHelper */
        $searchHelper = pluginApp(SearchHelper::class, [$facetValues, $plentyId, 'item', $lang]);
        $facetSearch = $searchHelper->getFacetSearch();
        $facetSearch->setName('facets');
        
        return $facetSearch;
    }
    
    public function getAggregations()
    {
        return [];
    }

    /**
     * @param array $options
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        $filters = [];
    
        /** @var WebshopFilterBuilder $webshopFilterBuilder */
        $webshopFilterBuilder = pluginApp(WebshopFilterBuilder::class);
        $defaultFilters = $webshopFilterBuilder->getFilters($options);
        $filters = array_merge( $filters, $defaultFilters );
        
        if( array_key_exists('categoryId', $options) && (int)$options['categoryId'] > 0)
        {
            /** @var CategoryFilter $categoryFilter */
            $categoryFilter = pluginApp(CategoryFilter::class);
            $categoryFilter->isInCategory($options['categoryId']);
            $filters[] = $categoryFilter;
        }
        elseif(array_key_exists('query', $options) && strlen($options['query']))
        {
            $lang = pluginApp(SessionStorageService::class)->getLang();
            
            /**
             * @var SearchFilter $searchFilter
             */
            $searchFilter = pluginApp(SearchFilter::class);
    
            $searchType = ElasticSearch::SEARCH_TYPE_FUZZY;
            if(array_key_exists('autocomplete', $options) && $options['autocomplete'] === true)
            {
                $searchFilter->setNamesString($options['query'], $lang);
            }
            else
            {
                $searchFilter->setSearchString($options['query'], $lang, $searchType, ElasticSearch::OR_OPERATOR);
                $searchFilter->setVariationNumber($options['query']);
            }
    
            $filters[] = $searchFilter;
        }
        
        return $filters;
    }
    
    private function getFacetValues($options)
    {
        if (array_key_exists('facets', $options) && count($options['facets'])) {
            $facetValues   = FacetBuilder::buildFacetValues($options['facets']);
            $activeFilters = explode(',', $options['facets']);
        } else {
            /**
             * @var Request $request
             */
            $request       = pluginApp(Request::class);
            $facetValues   = FacetBuilder::buildFacetValues($request->get('facets', ''));
            $activeFilters = explode(',', $request->get('facets', ''));
        }
        
        return [
            'facetValues' => $facetValues,
            'activeFilters' => $activeFilters
        ];
    }
    
    public function setOptions($options = [])
    {
        $this->options = $options;
        return $options;
    }

    /**
     * @param array $defaultResultFields
     * @return array
     */
    public function getResultFields($defaultResultFields)
    {
        return $defaultResultFields;
    }
}