<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\SortingHelper;

class CategoryItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $categoryId     = $options['categoryId'];
        $facets         = $options['facets'];
        $sorting        = SortingHelper::getCategorySorting( $options['sorting'] );

        $page           = (int) $options['page'];
        $itemsPerPage   = (int) $options['itemsPerPage'];

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp(VariationSearchFactory::class);

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withUrls()
            ->withPrices()
            ->isInCategory( $categoryId )
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList(false)
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->hasFacets( $facets )
            ->sortByMultiple( $sorting )
            ->setPage( $page, $itemsPerPage )
            ->groupByTemplateConfig();

        return $searchFactory;
    }
}