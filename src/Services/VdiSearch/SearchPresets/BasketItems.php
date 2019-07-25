<?php

namespace IO\Services\VdiSearch\SearchPresets;

use IO\Services\VdiSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;

/**
 * Class BasketItems
 *
 * Search preset for basket items.
 * Available options:
 * - variationIds: Ids of basket items to get data for
 * - quantities:   Quantity of each item to be considered when searching prices
 *
 * @package IO\Services\VdiSearch\SearchPresets
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
        $searchFactory = pluginApp( VariationSearchFactory::class )->withParts( self::getParts() );

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
    
    private static function getParts()
    {
        /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);
        $basePart->addLazyLoadParts(
            VariationBaseAttribute::TEXTS,
            VariationBaseAttribute::IMAGE,
            VariationBaseAttribute::ITEM,
            VariationBaseAttribute::STOCK
        );
        
        /** @var VariationImageAttribute $imagePart */
        $imagePart = app(VariationImageAttribute::class);
    
        return [
            $basePart,
            $imagePart,
        ];
    }
}
