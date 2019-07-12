<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\CategoryService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\Helper\SortingHelper;

/**
 * Class CrossSellingItems
 *
 * Search preset for cross selling items
 * Available options:
 * - itemId:    Id of the item to get cross selling items for
 * - relation:  The relation to consider when getting cross selling items
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class CrossSellingItems implements SearchPreset
{
    /**
     * @inheritdoc
     */
    public static function getSearchFactory($options)
    {
        $itemId = $options['itemId'];
        $relation = $options['relation'];
        $sorting = $options['sorting'];


        if(!isset($itemId) || !strlen($itemId))
        {
            $categoryService = pluginApp(CategoryService::class);
            $currentItem = $categoryService->getCurrentItem();
            $itemId = $currentItem['item']['id'] ?? 0;
        }
        /** @var ItemCrossSellingService $crossSellingService */
        $crossSellingService = pluginApp( ItemCrossSellingService::class );

        if ( $relation === null )
        {
            $relation = $crossSellingService->getType();
        }

        if(is_null($sorting))
        {
            $sorting = SortingHelper::splitPathAndOrder($crossSellingService->getSorting());
        }elseif(strlen($sorting))
        {
            $sorting = SortingHelper::getSorting($sorting);
        }


        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )
            ->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withImages()
            ->withPrices()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->groupByTemplateConfig()
            ->isCrossSellingItem( $itemId, $relation )
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->sortBy($sorting['path'], $sorting['order'])
            ->withLinkToContent()
            ->withGroupedAttributeValues()
            ->withReducedResults();

        return $searchFactory;
    }
}