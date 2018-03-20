<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemCrossSellingService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;

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

        if ( $relation === null )
        {
            /** @var ItemCrossSellingService $crossSellingService */
            $crossSellingService = pluginApp( ItemCrossSellingService::class );
            $relation = $crossSellingService->getType();
        }
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )
            ->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->groupByTemplateConfig()
            ->isCrossSellingItem( $itemId, $relation )
            ->hasNameInLanguage()
            ->hasPriceForCustomer();

        return $searchFactory;
    }
}