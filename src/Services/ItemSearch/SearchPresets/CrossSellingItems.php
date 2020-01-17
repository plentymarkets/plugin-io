<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\CategoryService;
use IO\Services\ItemCrossSellingService;
use IO\Contracts\VariationSearchFactoryContract as VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Contracts\SortingContract as SortingHelper;

/**
 * Class CrossSellingItems
 *
 * Search preset for cross selling items
 * Available options:
 * - itemId:    Id of the item to get cross selling items for
 * - relation:  The relation to consider when getting cross selling items
 *
 * @package IO\Services\ItemSearch\SearchPresets
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\CrossSellingItems
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
    
        /** @var SortingContract $sortingHelper */
        $sortingHelper = pluginApp(SortingHelper::class);
        
        if(is_null($sorting))
        {
            $sorting = $sortingHelper->splitPathAndOrder($crossSellingService->getSorting());
        }elseif(strlen($sorting))
        {
            $sorting = $sortingHelper->getSorting($sorting);
        }

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory->withResultFields(
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
            ->isHiddenInCategoryList( false )
            ->groupByTemplateConfig()
            ->isCrossSellingItem( $itemId, $relation )
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->sortBy($sorting['path'], $sorting['order'])
            ->withLinkToContent()
            ->withGroupedAttributeValues()
            ->withReducedResults(true);

        return $searchFactory;
    }
}
