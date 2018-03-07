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
        $quantities     = $options['variationQuantities'];
        
        $lang = null;
        if(isset($options['lang']) && strlen($options['lang']))
        {
            $lang = $options['lang'];
        }

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )
            ->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_BASKET_ITEM )
            );

        $searchFactory
            ->withLanguage($lang)
            ->withUrls()
            ->withImages()
            ->withPrices(['quantities' => $quantities])
            ->isVisibleForClient()
            ->isActive()
            ->hasVariationIds( $variationIds )
            ->setPage( 1, count( $variationIds ) );

        return $searchFactory;
    }
}