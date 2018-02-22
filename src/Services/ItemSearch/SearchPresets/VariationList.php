<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\SortingHelper;

class VariationList implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $variationIds = [];
        if ( array_key_exists('variationIds', $options ) )
        {
            $variationIds = $options['variationIds'];
        }

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory
            ->withImages()
            ->withPrices()
            ->withUrls()
            ->withLanguage()
            ->isVisibleForClient()
            ->isActive()
            ->hasPriceForCustomer()
            ->hasVariationIds( $variationIds );

        if ( array_key_exists( 'sorting', $options ) )
        {
            $sorting = SortingHelper::getSearchSorting( $options['sorting'] );
            $searchFactory->sortByMultiple( $sorting );
        }

        if ( array_key_exists('sortingField', $options ) )
        {
            $searchFactory->sortBy( $options['sortingField'], $options['sortingOrder'] );
        }

        if ( in_array('page', $options) && array_key_exists('itemsPerPage', $options ) )
        {
            $searchFactory->setPage( $options['page'], $options['itemsPerPage'] );
        }

        return $searchFactory;
    }
}