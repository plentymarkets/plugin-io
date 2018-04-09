<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\Helper\SortingHelper;

/**
 * Class VariationList
 *
 * Search preset for variation lists.
 * Available options:
 * - variationIds:  List of variations to receive.
 * - sorting:       Configuration value to get sorting for.
 * - sortingField:  Field to sort items by. Will be appended to sorting list if sorting configuration is defined.
 * - sortingOrder:  Order to sort items with ('asc', 'desc')
 * - page:          The current page
 * - itemsPerPage:  Number of items per page
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
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

        $searchFactory->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );

        $searchFactory
            ->withImages()
            ->withPrices()
            ->withUrls()
            ->withLanguage()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->hasPriceForCustomer()
            ->hasVariationIds( $variationIds );

        if ( array_key_exists( 'sorting', $options ) && $options['sorting'] !== null )
        {
            $sorting = SortingHelper::getSearchSorting( $options['sorting'] );
            $searchFactory->sortByMultiple( $sorting );
        }

        if ( array_key_exists('sortingField', $options ) && $options['sortingField'] !== null )
        {
            $searchFactory->sortBy( $options['sortingField'], $options['sortingOrder'] );
        }

        if ( array_key_exists('page', $options) && $options['page'] !== null
            && array_key_exists('itemsPerPage', $options ) && $options['itemsPerPage'] !== null )
        {
            $searchFactory->setPage( $options['page'], $options['itemsPerPage'] );
        }

        return $searchFactory;
    }
}