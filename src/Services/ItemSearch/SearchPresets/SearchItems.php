<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;

/**
 * Class SearchItems
 *
 * Search preset for search items.
 * Available options:
 * - query:         The search string
 * - facets:        Facet values of active facets
 * - sorting:       Configuration value from plugin config
 * - page:          The current page
 * - itemsPerPage:  Number of items per page
 * - priceMin:      Minimum price of the variations
 * - priceMax       Maximum price of the variations
 * - autocomplete:  Flag indicating if autocompletion should be used
 *
 *
 * @package IO\Services\ItemSearch\SearchPresets
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchItems
 */
class SearchItems implements SearchPreset
{
    /**
     * @inheritDoc
     */
    public static function getSearchFactory($options)
    {
        $query  = $options['query'];
        $facets = $options['facets'];

        /** @var SortingHelper $sortingHelper */
        $sortingHelper = pluginApp(SortingHelper::class);
        $sorting= $sortingHelper->getSearchSorting( $options['sorting'] );

        $page = 1;
        if ( array_key_exists('page', $options ) )
        {
            $page = (int) $options['page'];
        }

        $itemsPerPage = 20;
        if ( array_key_exists( 'itemsPerPage', $options ) )
        {
            $itemsPerPage = (int) $options['itemsPerPage'];
        }

        $priceMin = 0;
        if ( array_key_exists('priceMin', $options) )
        {
            $priceMin = (float) $options['priceMin'];
        }

        $priceMax = 0;
        if ( array_key_exists('priceMax', $options) )
        {
            $priceMax = (float) $options['priceMax'];
        }

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );



        if ( array_key_exists('autocomplete', $options ) && $options['autocomplete'] === true )
        {
            $searchFactory->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_AUTOCOMPLETE_ITEM_LIST )
            );
        }
        else
        {
            $searchFactory
                ->withDefaultImage()
                ->withImages()
                ->withPrices()
                ->hasPriceInRange($priceMin, $priceMax)
                ->hasFacets( $facets );

            $searchFactory->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );
        }

        $searchFactory
            ->withUrls()
            ->withLanguage()
            ->hasPriceForCustomer()
            ->hasNameInLanguage()
            ->isHiddenInCategoryList( false )
            ->isVisibleForClient()
            ->isActive()
            ->sortByMultiple( $sorting )
            ->setPage( $page, $itemsPerPage )
            ->groupByTemplateConfig()
            ->withGroupedAttributeValues()
            ->withReducedResults();

        $searchFactory->hasNameString($query);
        $searchFactory->hasSearchString( $query );

        return $searchFactory;
    }
}
