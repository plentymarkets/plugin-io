<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\Helper\SortingHelper;

class SearchItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $query  = $options['query'];
        $facets = $options['facets'];
        $sorting= SortingHelper::getSearchSorting( $options['sorting'] );

        $page           = (int) $options['page'];
        $itemsPerPage   = (int) $options['itemsPerPage'];


        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )
            ->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withPrices()
            ->withImages()
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList( false )
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
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