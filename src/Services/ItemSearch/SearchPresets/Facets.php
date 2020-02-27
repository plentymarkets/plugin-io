<?php

namespace IO\Services\ItemSearch\SearchPresets;

use Plenty\Modules\Webshop\ItemSearch\Factories\FacetSearchFactory;

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
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\Facets
 */
class Facets implements SearchPreset
{
    /**
     * @inheritDoc
     */
    public static function getSearchFactory($options)
    {
        /** @var FacetSearchFactory $searchFactory */
        $searchFactory = pluginApp(FacetSearchFactory::class)->create( $options['facets'] );
        $searchFactory
            ->withMinimumCount()
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList( false )
            ->groupByTemplateConfig()
            ->hasNameInLanguage()
            ->setPage(1,0)
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
