<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\FacetSearchFactory;

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