<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class BasketItems
 *
 * Search preset for basket items.
 * Available options:
 * - variationIds: Ids of basket items to get data for
 * - quantities:   Quantity of each item to be considered when searching prices
 *
 * @package IO\Services\ItemSearch\SearchPresets
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\BasketItems
 */
class BasketItems implements SearchPreset
{
    /**
     * @inheritdoc
     */
    public static function getSearchFactory($options)
    {
        $variationIds   = $options['variationIds'] ?? [];
        $quantities     = $options['quantities'];

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_BASKET_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withImages()
            ->withPropertyGroups()
            ->withOrderPropertySelectionValues()
            ->withDefaultImage()
            ->withBundleComponents()
            ->isVisibleForClient()
            ->isActive()
            ->hasVariationIds( $variationIds )
            ->setPage( 1, count( $variationIds ) )
            ->withReducedResults();

        if ( !is_null($quantities) )
        {
            $searchFactory->withPrices($quantities);
        }

        return $searchFactory;
    }
}
