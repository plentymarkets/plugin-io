<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
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
use Plenty\Plugin\Application;

class VariationSearchFactory extends BaseSearchFactory
{
    //
    // VARIATION BASE FILTERS
    //
    public function isActive()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isActive();
        return $this;
    }

    public function hasItemId( $itemId )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasItemId( $itemId );
        return $this;
    }

    public function hasItemIds( $itemIds )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasItemIds( $itemIds );
        return $this;
    }

    public function hasVariationId( $variationId )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasId( $variationId );
        return $this;
    }

    public function hasVariationIds( $variationIds )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasId( $variationIds );
        return $this;
    }

    public function isMain()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isMain();
        return $this;
    }

    public function isChild()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isChild();
        return $this;
    }

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
    public function hasAtLeastOnePrice( $priceIds = null )
    {
        /** @var SalesPriceFilter $priceFilter */
        $priceFilter = $this->createFilter( SalesPriceFilter::class );
        $priceFilter->hasAtLeastOnePrice( $priceIds );
        return $this;
    }

    public function hasPriceForCustomer()
    {
        /** @var PriceDetectService $priceDetectService */
        $priceDetectService = pluginApp( PriceDetectService::class );
        $this->hasAtLeastOnePrice( $priceDetectService->getPriceIdsForCustomer() );
        return $this;
    }

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

    //
    // EXTENSIONS
    //
    public function withUrls()
    {
        $this->withExtension( ItemUrlExtension::class );
        return $this;
    }

    public function withPrices()
    {
        $this->withExtension( PriceSearchExtension::class );
        return $this;
    }
}