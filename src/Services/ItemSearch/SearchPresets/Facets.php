<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\FacetSearchFactory;

/**
 * Class Facets
 *
 * Search preset for facets.
 * Available options:
 * - facets:        Values of active facets.
 * - categoryId:    Category Id to filter variations by.
 * - query:         Search string to get variations by.
 * - autocomplete:  Flag indicating if autocomplete search should be used (boolean). Will only be used if 'query' is defined.
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class Facets implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var FacetSearchFactory $searchFactory */
        $searchFactory = FacetSearchFactory::create( $options['facets'] );
        $searchFactory
            ->withMinimumCount()
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList( false )
            ->groupByTemplateConfig()
            ->hasNameInLanguage()
            ->hasPriceForCustomer();

        if ( array_key_exists('categoryId', $options ) && (int)$options['categoryId'] > 0 )
        {
            $searchFactory->isInCategory( $options['categoryId'] );
        }

        if ( array_key_exists('query', $options) && strlen($options['query'] ) )
        {
            if ( array_key_exists('autocomplete', $options ) && $options['autocomplete'] === true )
            {
                $searchFactory->hasNameString( $options['query'] );
            }
            else
            {
                $searchFactory->hasSearchString( $options['query'] );
            }
        }

        return $searchFactory;
    }
}