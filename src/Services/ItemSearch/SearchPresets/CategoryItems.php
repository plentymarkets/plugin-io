<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\Helper\SortingHelper;

/**
 * Class CategoryItems
 *
 * Search preset for category items.
 * Available options:
 * - categoryId:    Category id to get variations for
 * - facets:        Active facets to filter variations by
 * - sorting:       Configuration value from plugin config
 * - page:          Current page
 * - itemsPerPage:  Number of items per page
 * - priceMin:      Minimum price of the variations
 * - priceMax       Maximum price of the variations
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class CategoryItems implements SearchPreset
{
    /**
     * @inheritdoc
     */
    public static function getSearchFactory($options)
    {
        $categoryId     = $options['categoryId'];
        $facets         = $options['facets'];
        $sorting        = SortingHelper::getCategorySorting( $options['sorting'] );

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
        $searchFactory = pluginApp(VariationSearchFactory::class);

        $searchFactory->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->isInCategory( $categoryId )
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList(false)
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->hasPriceInRange($priceMin, $priceMax)
            ->hasFacets( $facets )
            ->sortByMultiple( $sorting )
            ->setPage( $page, $itemsPerPage )
            ->groupByTemplateConfig();

        return $searchFactory;
    }
}