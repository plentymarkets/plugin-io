<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\Helper\SortingHelper;

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
 */
class SearchItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $query  = $options['query'];
        $facets = $options['facets'];
        $sorting= SortingHelper::getSearchSorting( $options['sorting'] );

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

        $searchFactory->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withPrices()
            ->withImages()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList( false )
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->hasPriceInRange($priceMin, $priceMax)
            ->hasFacets( $facets )
            ->sortByMultiple( $sorting )
            ->setPage( $page, $itemsPerPage )
            ->groupByTemplateConfig();

        if ( array_key_exists('autocomplete', $options ) && $options['autocomplete'] === true )
        {
            $searchFactory->hasNameString( $query );
        }
        else
        {
            $searchFactory->hasSearchString( $query );
        }

        return $searchFactory;
    }
}