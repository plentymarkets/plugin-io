<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemCrossSellingService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;

class CrossSellingItems implements SearchPreset
{
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
            ->isVisibleForClient()
            ->isActive()
            ->groupByTemplateConfig()
            ->isCrossSellingItem( $itemId, $relation )
            ->hasNameInLanguage()
            ->hasPriceForCustomer();

        return $searchFactory;
    }
}