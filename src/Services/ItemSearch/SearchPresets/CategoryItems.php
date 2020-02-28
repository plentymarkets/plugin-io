<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;

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
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\CategoryItems
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

        /** @var SortingHelper $sortingHelper */
        $sortingHelper = pluginApp(SortingHelper::class);
        $sorting        = $sortingHelper->getCategorySorting( $options['sorting'] );

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
            ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
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
            ->groupByTemplateConfig()
            ->withLinkToContent()
            ->withGroupedAttributeValues()
            ->withReducedResults();

        return $searchFactory;
    }
}
