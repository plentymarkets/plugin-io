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
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\ShopTheLookPreset
 */
class ShopTheLookPreset implements SearchPreset
{
    /**
     * @inheritdoc
     */
    public static function getSearchFactory($options)
    {
        $variationId   = $options['variationId'] ?? null;
        
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_SHOPTHELOOK_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withImages()
            // ->withPropertyGroups()
            // ->withOrderPropertySelectionValues()
            // ->withVariationProperties()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            // ->withBundleComponents()
            // ->withAvailability()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->withLinkToContent()
            ->withReducedResults()
            // ->withTags()
            ->hasVariationId( $variationId )
            ->setPage( 1, 1 );

        return $searchFactory;
    }
}