<?php

namespace IO\Services\VdiSearch\Factories;

use IO\Contracts\VariationSearchFactoryContract;
use IO\Helper\CurrencyConverter;
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
use IO\Services\ItemSearch\Extensions\VariationAttributeMapExtension;
use IO\Services\ItemSearch\Extensions\VariationPropertyExtension;
use IO\Services\ItemSearch\Helper\FacetExtensionContainer;
use IO\Services\ItemSearch\Mutators\OrderPropertySelectionValueMutator;
use IO\Services\PriceDetectService;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;

use Plenty\Modules\Pim\SearchService\Filter\CategoryFilter;
use Plenty\Modules\Pim\SearchService\Filter\ClientFilter;
use Plenty\Modules\Pim\SearchService\Filter\CrossSellingFilter;
use Plenty\Modules\Pim\SearchService\Filter\PriceFilter;
use Plenty\Modules\Pim\SearchService\Filter\SalesPriceFilter;
use Plenty\Modules\Pim\SearchService\Filter\TagFilter;
use Plenty\Modules\Pim\SearchService\Filter\TextFilter;
use Plenty\Modules\Pim\SearchService\Filter\VariationBaseFilter;
use Plenty\Modules\Pim\SearchService\Filter\PropertyFilter;

use Plenty\Modules\Item\Search\Mutators\ImageDomainMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Mutators\VariationPropertyGroupMutator;
use Plenty\Modules\Pim\SearchService\Helper\FacetHelper;
use Plenty\Modules\Pim\SearchService\Query\ManagedSearchQuery;
use Plenty\Modules\Pim\SearchService\Query\NameAutoCompleteQuery;
use Plenty\Plugin\Application;

/**
 * Class VariationSearchFactory
 *
 * Concrete factory to build variation searches
 *
 * @package IO\Services\ItemSearch\Factories
 */
class VariationSearchFactory extends BaseSearchFactory implements VariationSearchFactoryContract
{
    private $isAdminPreview = false;

    public function __construct()
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);
        $this->isAdminPreview = $app->isAdminPreview();
    }

    /**
     * @param $isAdminPreview
     * @return $this
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
     * Filter variations by multiple availability ids.
     *
     * @param int[]   $availabilityIds    List of availability ids to filter by.
     *
     * @return $this
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
     */
    public function isVisibleForClient( $clientId = null )
    {
        if(!$this->isAdminPreview)
        {
            if ( $clientId === null )
            {
                $clientId = pluginApp( Application::class )->getPlentyId();
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
     */
    public function hasNameInLanguage( $type = TextFilter::FILTER_ANY_NAME, $lang = null)
    {
        if(!$this->isAdminPreview)
        {
            if ( $lang === null )
            {
                $lang = pluginApp(SessionStorageService::class)->getLang();
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
     */
    public function hasPriceForCustomer()
    {
        if(!$this->isAdminPreview)
        {
            /** @var PriceDetectService $priceDetectService */
            $priceDetectService = pluginApp( PriceDetectService::class );
            $this->hasAtLeastOnePrice( $priceDetectService->getPriceIdsForCustomer() );
        }

        return $this;
    }

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

            /** @var PriceDetectService  $priceDetectService */
            $priceDetectService = pluginApp(PriceDetectService::class);
            $prices = $priceDetectService->getPriceIdsForCustomer();

            /** @var PriceFilter $priceRangeFilter */
            $priceRangeFilter = $this->createFilter(PriceFilter::class);
            $priceRangeFilter->betweenByPriceId($prices, $priceMin, $priceMax);
        }

        return $this;
    }

    public function hasTag($tagId)
    {
        return $this->hasAnyTag([$tagId]);
    }

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
            $this->groupBy( 'ids.itemAttributeValue' );
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

        /** @var FacetHelper $facetHelper */
        $facetHelper = app(FacetHelper::class, [
            'facetValuesSelected' => is_array($facetValues) ? $facetValues : [],
            'collapse' => false, //TODO collapse
            'lang' => $lang,
            'plentyId' => $clientId
        ]);

        $this->withFilter($facetHelper->getFilter());

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
     *
     * @return $this
     */
    public function hasSearchString( $query, $lang = null, $a = '', $b = '')
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        $this->createFilter(ManagedSearchQuery::class, ['query' => $query, 'lang' => $lang]);

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

        $this->createFilter( NameAutoCompleteQuery::class, ['query' => $query, 'lang' => $lang]);

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
     */
    public function withAttributes()
    {
        $this->withExtension( VariationAttributeMapExtension::class );

        return $this;
    }

    public function withPropertyGroups()
    {
        $propertyGroupMutator = pluginApp(VariationPropertyGroupMutator::class);
        $this->withMutator($propertyGroupMutator);

        return $this;
    }

    public function withOrderPropertySelectionValues()
    {
        $orderPropertySelectionValueMutator = pluginApp(OrderPropertySelectionValueMutator::class);
        $this->withMutator($orderPropertySelectionValueMutator);

        return $this;
    }

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

    public function withBundleComponents()
    {
        $this->withExtension( BundleComponentExtension::class );
        return $this;
    }

    public function withLinkToContent()
    {
        $this->withExtension( ContentCacheVariationLinkExtension::class );
        return $this;
    }

    public function withGroupedAttributeValues()
    {
        $this->withExtension( GroupedAttributeValuesExtension::class );
        return $this;
    }

    public function withReducedResults()
    {
        $this->withExtension(ReduceDataExtension::class);
        return $this;
    }

    public function withAvailability()
    {
        $this->withExtension(AvailabilityExtension::class);
        return $this;
    }
}

