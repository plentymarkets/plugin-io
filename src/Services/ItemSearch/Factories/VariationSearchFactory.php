<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\ItemSearch\Extensions\CurrentCategoryExtension;
use IO\Services\ItemSearch\Extensions\ItemDefaultImage;
use IO\Services\ItemSearch\Extensions\ItemUrlExtension;
use IO\Services\ItemSearch\Extensions\PriceSearchExtension;
use IO\Services\PriceDetectService;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\BaseCollapse;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Aggregations\ItemCardinalityAggregation;
use Plenty\Modules\Item\Search\Aggregations\ItemCardinalityAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\CrossSellingFilter;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Helper\SearchHelper;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Mutators\VariationPropertyGroupMutator;
use Plenty\Plugin\Application;

/**
 * Class VariationSearchFactory
 *
 * Concrete factory to build variation searches
 *
 * @package IO\Services\ItemSearch\Factories
 */
class VariationSearchFactory extends BaseSearchFactory
{
    //
    // VARIATION BASE FILTERS
    //
    /**
     * Filter active variations
     *
     * @return $this
     */
    public function isActive()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isActive();
        return $this;
    }

    /**
     * Filter variation by a single item id
     *
     * @param int   $itemId   Item id to filter by.
     *
     * @return $this
     */
    public function hasItemId( $itemId )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasItemId( $itemId );
        return $this;
    }

    /**
     * Filter variations by multiple item ids
     *
     * @param int[]     $itemIds    List of item ids to filter by.
     *
     * @return $this
     */
    public function hasItemIds( $itemIds )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasItemIds( $itemIds );
        return $this;
    }

    /**
     * Filter variation by a single variation id.
     *
     * @param int   $variationId    The variation id to filter by.
     *
     * @return $this
     */
    public function hasVariationId( $variationId )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasId( $variationId );
        return $this;
    }

    /**
     * Filter variations by multiple variation ids.
     *
     * @param int[]     $variationIds   List of variation ids to filter by.
     *
     * @return $this
     */
    public function hasVariationIds( $variationIds )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasIds( $variationIds );
        return $this;
    }

    /**
     * Filter only main variations
     *
     * @return $this
     */
    public function isMain()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isMain();
        return $this;
    }

    /**
     * Filter only child variations
     *
     * @return $this
     */
    public function isChild()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isChild();
        return $this;
    }

    /**
     * Filter by visibility in category list.
     *
     * @param bool    $isHidden     Visibility in category list to filter by.
     *
     * @return $this
     */
    public function isHiddenInCategoryList( $isHidden = true )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isHiddenInCategoryList( $isHidden );
        return $this;
    }

    //
    // CLIENT FILTERS
    //
    /**
     * Filter variations by visibility for client
     *
     * @param null|int  $clientId   The client id to filter by. If null, default client id on application will be used.
     *
     * @return $this
     */
    public function isVisibleForClient( $clientId = null )
    {
        if ( $clientId === null )
        {
            $clientId = pluginApp( Application::class )->getPlentyId();
        }
        /** @var ClientFilter $clientFilter */
        $clientFilter = $this->createFilter( ClientFilter::class );
        $clientFilter->isVisibleForClient( $clientId );
        return $this;
    }

    //
    // TEXT FILTERS
    //
    /**
     * Filter variations having texts in a given language.
     *
     * @param string        $type   The text field to filter by ('hasAny', 'hasName1', 'hasName2', 'hasName3')
     * @param null|string   $lang   The language to filter by. If null, language defined in session will be used.
     *
     * @return $this
     */
    public function hasNameInLanguage( $type = TextFilter::FILTER_ANY_NAME, $lang = null)
    {
        if ( $lang === null )
        {
            $lang = pluginApp(SessionStorageService::class)->getLang();
        }

        $langMap = [
            'de' => TextFilter::LANG_DE,
            'en' => TextFilter::LANG_EN,
            'fr' => TextFilter::LANG_FR,
        ];

        if ( array_key_exists( $lang, $langMap ) )
        {
            $lang = $langMap[$lang];
        }

        if ( $lang !== TextFilter::LANG_DE && $lang !== TextFilter::LANG_EN && $lang !== TextFilter::LANG_FR )
        {
            $lang = TextFilter::LANG_DE;
        }
        /** @var TextFilter $textFilter */
        $textFilter = $this->createFilter(TextFilter::class);
        $textFilter->hasNameInLanguage( $lang, $type );
        return $this;
    }

    //
    // CATEGORY FILTERS
    //
    /**
     * Filter variations contained in a category.
     *
     * @param int   $categoryId     A category id to filter variations by.
     *
     * @return $this
     */
    public function isInCategory( $categoryId )
    {
        /** @var CategoryFilter $categoryFilter */
        $categoryFilter = $this->createFilter( CategoryFilter::class );
        $categoryFilter->isInCategory( $categoryId );
        return $this;
    }

    //
    // PRICE FILTERS
    //
    /**
     * Filter variations having at least on price.
     *
     * @param int[]     $priceIds   List of price ids to filter variations by
     *
     * @return $this
     */
    public function hasAtLeastOnePrice( $priceIds )
    {
        /** @var SalesPriceFilter $priceFilter */
        $priceFilter = $this->createFilter( SalesPriceFilter::class );
        $priceFilter->hasAtLeastOnePrice( $priceIds );
        return $this;
    }

    /**
     * Filter variations having at least one price accessible by current customer.
     *
     * @return $this
     */
    public function hasPriceForCustomer()
    {
        /** @var PriceDetectService $priceDetectService */
        $priceDetectService = pluginApp( PriceDetectService::class );
        $this->hasAtLeastOnePrice( $priceDetectService->getPriceIdsForCustomer() );
        return $this;
    }

    /**
     * Group results depending on a config value.
     *
     * @param string $configKey     The config key containing the grouping method: ('all', 'combined', 'main', 'child')
     *
     * @return $this
     */
    public function groupByTemplateConfig( $configKey = 'item.variation_show_type' )
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $variationShowType = $templateConfigService->get($configKey);
        if ($variationShowType === 'combined')
        {
            $this->groupBy( 'ids.itemId' );
        }
        else if ( $variationShowType === 'main' )
        {
            $this->isMain();
        }
        else if ( $variationShowType === 'child' )
        {
            $this->isChild();
        }

        return $this;
    }

    /**
     * Filter variations having a cross selling relation to a given item.
     *
     * @param int       $itemId     Item id to filter cross selling items for
     * @param string    $relation   The relation of cross selling items.
     *
     * @return $this
     */
    public function isCrossSellingItem( $itemId, $relation )
    {
        /** @var CrossSellingFilter $crossSellingFilter */
        $crossSellingFilter = pluginApp( CrossSellingFilter::class, [$itemId] );
        $crossSellingFilter->hasRelation( $relation );
        $this->withFilter( $crossSellingFilter );
        return $this;
    }

    //
    // FACET FILTERS
    //
    /**
     * Filter variations by facets.
     *
     * @param string|array   $facetValues   List of facet values. If string is given, it will be exploded by ';'
     * @param int            $clientId      Client id to filter facets by. If null, default client id from application will be used.
     * @param string         $lang          Language to filter facets by. If null, active language from session will be used.
     *
     * @return $this
     */
    public function hasFacets( $facetValues, $clientId = null, $lang = null )
    {
        if ( $clientId === null )
        {
            $clientId = pluginApp( Application::class )->getPlentyId();
        }

        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        if ( is_string( $facetValues ) )
        {
            $facetValues = explode(",", $facetValues );
        }

        $facetValues = array_map(function($facetValue) {
            return (int) $facetValue;
        }, $facetValues);

        /** @var SearchHelper $searchHelper */
        $searchHelper = pluginApp( SearchHelper::class, [$facetValues, $clientId, 'item', $lang] );
        $this->withFilter( $searchHelper->getFacetFilter() );

        $facetExtensions = pluginApp( FacetExtensionContainer::class )->getFacetExtensions();
        foreach( $facetExtensions as $facetExtension )
        {
            if ( $facetExtension instanceof FacetExtension )
            {
                $facetAggregation = $facetExtension->getAggregation();
                if ( $facetExtension !== null )
                {
                    $this->withAggregation( $facetAggregation );
                }

                $filter = $facetExtension->extractFilterParams( $facetValues );
                if( $filter !== null )
                {
                    $this->withFilter( $filter );
                }
            }
        }

        return $this;
    }

    //
    // SEARCH
    //
    /**
     * Filter variations by given search string.
     * @param string    $query      The search string to filter variations by
     * @param string    $lang       The language to apply search on. If null, default language from session will be used
     * @param string    $searchType Type of the search ('exact', 'fuzzy', 'autocomplete')
     * @param string    $operator   Operator ot be used for search
     *
     * @return $this
     */
    public function hasSearchString( $query, $lang = null, $searchType = ElasticSearch::SEARCH_TYPE_FUZZY, $operator = ElasticSearch::OR_OPERATOR )
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        if ( $searchType !== ElasticSearch::SEARCH_TYPE_FUZZY
            && $searchType !== ElasticSearch::SEARCH_TYPE_AUTOCOMPLETE
            && $searchType !== ElasticSearch::SEARCH_TYPE_EXACT )
        {
            $searchType = ElasticSearch::SEARCH_TYPE_FUZZY;
        }

        if ( $operator !== ElasticSearch::OR_OPERATOR && $operator !== ElasticSearch::AND_OPERATOR )
        {
            $operator = ElasticSearch::OR_OPERATOR;
        }

        /** @var SearchFilter $searchFilter */
        $searchFilter = $this->createFilter(SearchFilter::class);
        $searchFilter->setSearchString($query, $lang, $searchType, $operator);

        return $this;
    }

    /**
     * Filter variations by searching names
     *
     * @param string    $query  The search string
     * @param string    $lang   Language to apply search on. If null, default language from session will be used.
     *
     * @return $this
     */
    public function hasNameString( $query, $lang = null )
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        /** @var SearchFilter $searchFilter */
        $searchFilter = $this->createFilter( SearchFilter::class );
        $searchFilter->setNamesString( $query, $lang );

        return $this;
    }

    //
    // MUTATORS
    //
    /**
     * Only request given language.
     *
     * @param string    $lang   Language to get texts for. If null, default language from session will be used.
     *
     * @return $this
     */
    public function withLanguage( $lang = null )
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [$lang]]);
        $this->withMutator( $languageMutator );

        return $this;
    }

    /**
     * Include images in result
     *
     * @param int   $clientId   The client id to get images for. If null, default client id from application will be used.
     *
     * @return $this
     */
    public function withImages( $clientId = null )
    {
        if ( $clientId === null )
        {
            $clientId = pluginApp( Application::class )->getPlentyId();
        }

        $imageMutator = pluginApp(ImageMutator::class);
        $imageMutator->addClient( $clientId );
        $this->withMutator( $imageMutator );

        return $this;
    }
    
    public function withPropertyGroups()
    {
        $propertyGroupMutator = pluginApp(VariationPropertyGroupMutator::class);
        $this->withMutator($propertyGroupMutator);
        
        return $this;
    }

    //
    // EXTENSIONS
    //
    /**
     * Append URLs to result.
     * If not URL is stored in item result, a new URL will be generated and written to item data.
     *
     * @return $this
     */
    public function withUrls()
    {
        $this->withExtension( ItemUrlExtension::class );
        return $this;
    }

    /**
     * Append prices to result.
     *
     * @param array $params     Params to be passed to price search.
     *
     * @return $this
     */
    public function withPrices( $params = [] )
    {
        $this->withExtension( PriceSearchExtension::class, $params );
        return $this;
    }

    /**
     * Set result as current category
     *
     * @return $this
     */
    public function withCurrentCategory()
    {
        $this->withExtension( CurrentCategoryExtension::class );
        return $this;
    }

    /**
     * Append default item image if images are requested by result fields and item does not have any image
     *
     * @return $this
     */
    public function withDefaultImage()
    {
        $this->withExtension( ItemDefaultImage::class );
        return $this;
    }
}