<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Helper\CurrencyConverter;
use IO\Helper\Utils;
use IO\Helper\VatConverter;
use IO\Services\ItemSearch\Contracts\FacetExtension;
use IO\Services\ItemSearch\Extensions\AvailabilityExtension;
use IO\Services\ItemSearch\Extensions\GroupedAttributeValuesExtension;
use IO\Services\ItemSearch\Extensions\BundleComponentExtension;
use IO\Services\ItemSearch\Extensions\ContentCacheVariationLinkExtension;
use IO\Services\ItemSearch\Extensions\CurrentCategoryExtension;
use IO\Services\ItemSearch\Extensions\ItemDefaultImage;
use IO\Services\ItemSearch\Extensions\ItemUrlExtension;
use IO\Services\ItemSearch\Extensions\PriceSearchExtension;
use IO\Services\ItemSearch\Extensions\ReduceDataExtension;
use IO\Services\ItemSearch\Extensions\TagExtension;
use IO\Services\ItemSearch\Extensions\VariationAttributeMapExtension;
use IO\Services\ItemSearch\Extensions\VariationPropertyExtension;
use IO\Services\ItemSearch\Mutators\OrderPropertySelectionValueMutator;
use IO\Services\PriceDetectService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\CrossSellingFilter;
use Plenty\Modules\Item\Search\Filter\PriceFilter;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Filter\TagFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Filter\PropertyFilter;
use Plenty\Modules\Item\Search\Helper\SearchHelper;
use Plenty\Modules\Item\Search\Mutators\ImageDomainMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Mutators\VariationPropertyGroupMutator;
use Plenty\Modules\Webshop\Contracts\PriceDetectRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\FacetExtensionContainer;
use Plenty\Plugin\Application;

/**
 * Class VariationSearchFactory
 *
 * Concrete factory to build variation searches
 *
 * @package IO\Services\ItemSearch\Factories
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory
 */
class VariationSearchFactory extends BaseSearchFactory
{
    private $isAdminPreview = false;

    public function __construct()
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);
        $this->isAdminPreview = $app->isAdminPreview();
    }

    /**
     * @param bool $isAdminPreview
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::setAdminPreview()
     */
    public function setAdminPreview($isAdminPreview)
    {
        $this->isAdminPreview = $isAdminPreview;
        return $this;
    }

    //
    // VARIATION BASE FILTERS
    //
    /**
     * Filter active variations
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isActive()
     */
    public function isActive()
    {
        if(!$this->isAdminPreview)
        {
            /** @var VariationBaseFilter $variationFilter */
            $variationFilter = $this->createFilter( VariationBaseFilter::class );
            $variationFilter->isActive();
        }

        return $this;
    }

    /**
     * Filter inactive variations
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isInactive()
     */
    public function isInactive()
    {
        if(!$this->isAdminPreview)
        {
            /** @var VariationBaseFilter $variationFilter */
            $variationFilter = $this->createFilter( VariationBaseFilter::class );
            $variationFilter->isInactive();
        }

        return $this;
    }

    /**
     * Filter variation by a single item id
     *
     * @param int   $itemId   Item id to filter by.
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasItemId()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasItemIds()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasVariationId()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasVariationIds()
     */
    public function hasVariationIds( $variationIds )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasIds( $variationIds );
        return $this;
    }

    /**
     * Filter variations by multiple availability ids.
     *
     * @param int[]   $availabilityIds    List of availability ids to filter by.
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasAtLeastOneAvailability()
     */
    public function hasAtLeastOneAvailability( $availabilityIds )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasAtLeastOneAvailability( $availabilityIds );
        return $this;
    }

    /**
     * Filter variations by multiple availability ids.
     *
     * @param int     $supplierId     The supplier id to filter by.
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasSupplier()
     */
    public function hasSupplier( $supplierId )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasSupplier( $supplierId );
        return $this;
    }

    /**
     * Filter manufacturers by id.
     *
     * @param int $manufacturerId To filter by manufacturer
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasManufacturer()
     */
    public function hasManufacturer( $manufacturerId )
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->hasManufacturer( $manufacturerId );
        return $this;
    }

    /**
     * Filter variations by multiple property ids.
     *
     * @param int[]     $propertyIds     The property ids to filter by.
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasEachProperty()
     */
    public function hasEachProperty( $propertyIds )
    {
        /** @var PropertyFilter $propertyFilter */
        $propertyFilter = $this->createFilter( PropertyFilter::class );
        $propertyFilter->hasEachProperty( $propertyIds );
        return $this;
    }

    /**
     * Filter only main variations
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isMain()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isChild()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isHiddenInCategoryList()
     */
    public function isHiddenInCategoryList( $isHidden = true )
    {
        if(!$this->isAdminPreview)
        {
            /** @var VariationBaseFilter $variationFilter */
            $variationFilter = $this->createFilter( VariationBaseFilter::class );
            $variationFilter->isHiddenInCategoryList( $isHidden );
        }

        return $this;
    }

    /**
     * Filter variations by isSalable flag
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isSalable()
     */
    public function isSalable()
    {
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->createFilter( VariationBaseFilter::class );
        $variationFilter->isSalable();

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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isVisibleForClient()
     */
    public function isVisibleForClient( $clientId = null )
    {
        if(!$this->isAdminPreview)
        {
            if ( $clientId === null )
            {
                $clientId = Utils::getPlentyId();
            }
            /** @var ClientFilter $clientFilter */
            $clientFilter = $this->createFilter( ClientFilter::class );
            $clientFilter->isVisibleForClient( $clientId );
        }

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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasNameInLanguage()
     */
    public function hasNameInLanguage( $type = TextFilter::FILTER_ANY_NAME, $lang = null)
    {
        if(!$this->isAdminPreview)
        {
            if ( $lang === null )
            {
                $lang = Utils::getLang();
            }

            $langMap = [
                'de' => 'german',
                'en' => 'english',
                'fr' => 'french',
                'bg' => 'bulgarian',
                'it' => 'italian',
                'es' => 'spanish',
                'tr' => 'turkish',
                'nl' => 'dutch',
                // 'pl' => '',
                'pt' => 'portuguese',
                'nn' => 'norwegian',
                'ro' => 'romanian',
                'da' => 'danish',
                'se' => 'swedish',
                'cz' => 'czech',
                'ru' => 'russian',
                //'sk' => '',
                //'cn' => '',
                //'vn' => '',
            ];

            if ( array_key_exists( $lang, $langMap ) )
            {
                $lang = $langMap[$lang];
            }
            else
            {
                $lang = TextFilter::LANG_DE;
            }
            /** @var TextFilter $textFilter */
            $textFilter = $this->createFilter(TextFilter::class);
            $textFilter->hasNameInLanguage( $lang, $type );
        }

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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isInCategory()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasAtLeastOnePrice()
     */
    public function hasAtLeastOnePrice( $priceIds )
    {
        if(!$this->isAdminPreview)
        {
            /** @var SalesPriceFilter $priceFilter */
            $priceFilter = $this->createFilter( SalesPriceFilter::class );
            $priceFilter->hasAtLeastOnePrice( $priceIds );
        }

        return $this;
    }

    /**
     * Filter variations having at least one price accessible by current customer.
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasPriceForCustomer()
     */
    public function hasPriceForCustomer()
    {
        if(!$this->isAdminPreview)
        {
            /** @var PriceDetectService $priceDetectService */
            $priceDetectService = pluginApp(PriceDetectService::class);

            $this->hasAtLeastOnePrice( $priceDetectService->getPriceIdsForCustomer() );
        }

        return $this;
    }

    /**
     * @param float $priceMin
     * @param float $priceMax
     * @return $this
     * @throws \ErrorException
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasPriceInRange()
     */
    public function hasPriceInRange($priceMin, $priceMax)
    {
        if( !( (float)$priceMin == 0 && (float)$priceMax == 0 ) )
        {
            /** @var CurrencyConverter $currencyConverter */
            $currencyConverter = pluginApp(CurrencyConverter::class);

            /** @var VatConverter $vatConverter */
            $vatConverter = pluginApp(VatConverter::class);

            $priceMin = $vatConverter->convertToGross($currencyConverter->convertToDefaultCurrency((float)$priceMin));
            $priceMax = $vatConverter->convertToGross($currencyConverter->convertToDefaultCurrency((float)$priceMax));

            if((float)$priceMax == 0)
            {
                $priceMax = null;
            }

            /** @var PriceFilter $priceRangeFilter */
            $priceRangeFilter = $this->createFilter(PriceFilter::class);
            $priceRangeFilter->betweenByClient($priceMin, $priceMax, Utils::getPlentyId());
        }

        return $this;
    }

    /**
     * @param int $tagId
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasTag()
     */
    public function hasTag($tagId)
    {
        return $this->hasAnyTag([$tagId]);
    }

    /**
     * @param int $tagIds
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasAnyTag()
     */
    public function hasAnyTag($tagIds)
    {
        /** @var TagFilter $tagFilter */
        $tagFilter = $this->createFilter(TagFilter::class);
        if ( count($tagIds) === 1 )
        {
            $tagFilter->hasTag((int) $tagIds[0]);
        }
        else if ( count($tagIds) > 1 )
        {
            $tagFilter->hasAnyTag( $tagIds );
        }
        return $this;
    }

    /**
     * Group results depending on a config value.
     *
     * @param string $key
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::groupByTemplateConfig()
     */
    public function groupByTemplateConfig($key = 'ids.itemAttributeValue')
    {
        $this->groupBy($key);

        return $this;
    }

    /**
     * Filter variations having a cross selling relation to a given item.
     *
     * @param int       $itemId     Item id to filter cross selling items for
     * @param string    $relation   The relation of cross selling items.
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::isCrossSellingItem()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasFacets()
     */
    public function hasFacets( $facetValues, $clientId = null, $lang = null )
    {
        if ( $clientId === null )
        {
            $clientId = Utils::getPlentyId();
        }

        if ( $lang === null )
        {
            $lang = Utils::getLang();
        }

        if ( is_string( $facetValues ) )
        {
            $facetValues = explode(",", $facetValues );
        }

        /** @var SearchHelper $searchHelper */
        $searchHelper = pluginApp( SearchHelper::class, [$facetValues, $clientId, 'item', $lang] );
        $this->withFilter( $searchHelper->getFacetFilter() );

        /** @var FacetExtensionContainer $facetExtensionContainer */
        $facetExtensionContainer = pluginApp(FacetExtensionContainer::class);

        $facetExtensions = $facetExtensionContainer->getFacetExtensions();
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasSearchString()
     */
    public function hasSearchString( $query, $lang = null, $searchType = ElasticSearch::SEARCH_TYPE_EXACT, $operator = ElasticSearch::OR_OPERATOR )
    {
        if ( $lang === null )
        {
            $lang = Utils::getLang();
        }

        if ( $searchType !== ElasticSearch::SEARCH_TYPE_FUZZY
            && $searchType !== ElasticSearch::SEARCH_TYPE_AUTOCOMPLETE
            && $searchType !== ElasticSearch::SEARCH_TYPE_EXACT )
        {
            $searchType = ElasticSearch::SEARCH_TYPE_EXACT;
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::hasNameString()
     */
    public function hasNameString( $query, $lang = null )
    {
        if ( $lang === null )
        {
            $lang = Utils::getLang();
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withLanguage()
     */
    public function withLanguage( $lang = null )
    {
        if ( $lang === null )
        {
            $lang = Utils::getLang();
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withImages()
     */
    public function withImages( $clientId = null )
    {
        if ( $clientId === null )
        {
            $clientId = Utils::getPlentyId();
        }

        $imageMutator = pluginApp(ImageMutator::class);
        /**
         * @var ImageMutator $imageMutator
         */
        $imageMutator->setSorting(ImageMutator::SORT_POSITION);
        $imageMutator->addClient( $clientId );
        $this->withMutator( $imageMutator );

        /** @var ImageDomainMutator $imageDomainMutator */
        $imageDomainMutator = pluginApp(ImageDomainMutator::class);
        $imageDomainMutator->setClient($clientId);
        $this->withMutator($imageDomainMutator);

        return $this;
    }

    /**
     * Includes VariatonAttributeMap for variation select
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withAttributes()
     */
    public function withAttributes()
    {
        $this->withExtension( VariationAttributeMapExtension::class );

        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withPropertyGroups()
     */
    public function withPropertyGroups()
    {
        $propertyGroupMutator = pluginApp(VariationPropertyGroupMutator::class);
        $this->withMutator($propertyGroupMutator);

        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withOrderPropertySelectionValues()
     */
    public function withOrderPropertySelectionValues()
    {
        $orderPropertySelectionValueMutator = pluginApp(OrderPropertySelectionValueMutator::class);
        $this->withMutator($orderPropertySelectionValueMutator);

        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withVariationProperties()
     */
    public function withVariationProperties()
    {
        $this->withExtension(VariationPropertyExtension::class);

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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withUrls()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withPrices()
     */
    public function withPrices( $quantities = [] )
    {
        $this->withExtension( PriceSearchExtension::class, [
            'quantities' => $quantities
        ]);
        return $this;
    }

    /**
     * Set result as current category
     *
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withCurrentCategory()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withDefaultImage()
     */
    public function withDefaultImage()
    {
        $this->withExtension( ItemDefaultImage::class );
        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withBundleComponents()
     */
    public function withBundleComponents()
    {
        $this->withExtension( BundleComponentExtension::class );
        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withLinkToContent()
     */
    public function withLinkToContent()
    {
        $this->withExtension( ContentCacheVariationLinkExtension::class );
        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withGroupedAttributeValues()
     */
    public function withGroupedAttributeValues()
    {
        $this->withExtension( GroupedAttributeValuesExtension::class );
        return $this;
    }

    /**
     * @param bool $removeProperties
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withReducedResults()
     */
    public function withReducedResults($removeProperties = false)
    {
        $this->withExtension(ReduceDataExtension::class, ['removeProperties' => $removeProperties]);
        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withAvailability()
     */
    public function withAvailability()
    {
        $this->withExtension(AvailabilityExtension::class);
        return $this;
    }

    /**
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory::withTags()
     */
    public function withTags()
    {
        $this->withExtension(TagExtension::class);
        return $this;
    }

}

