<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;

/**
 * Class BasketItems
 *
 * Search preset for basket items.
 * Available options:
 * - variationIds:          Ids of basket items to get data for
 * - variationQuantities:   Quantity of each item to be considered when searching prices
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class BasketItems implements SearchPreset
{
    /**
     * @inheritdoc
     */
    public static function getSearchFactory($options)
    {
        $variationIds   = $options['variationIds'];
        $quantities     = $options['quantities'];

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )
            ->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_BASKET_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withImages()
            ->withPrices(['quantities' => $quantities])
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->hasVariationIds( $variationIds )
            ->setPage( 1, count( $variationIds ) );

        return $searchFactory;
    }
}