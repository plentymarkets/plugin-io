<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Contracts\VariationSearchFactoryContract as VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

/**
 * Class BasketItems
 *
 * Search preset for basket items.
 * Available options:
 * - variationIds: Ids of basket items to get data for
 * - quantities:   Quantity of each item to be considered when searching prices
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
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_BASKET_ITEM )
            )
            ->withParts(self::getParts());

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
            VariationBaseAttribute::AVAILABILITY,
            VariationBaseAttribute::CROSS_SELLING,
            VariationBaseAttribute::IMAGE,
            VariationBaseAttribute::ITEM,
            VariationBaseAttribute::PROPERTY,
            VariationBaseAttribute::SERIAL_NUMBER,
            VariationBaseAttribute::STOCK
        );
        
        /** @var VariationSalesPriceAttribute $pricePart */
        $pricePart = app(VariationSalesPriceAttribute::class);
        $pricePart->addLazyLoadParts(VariationSalesPriceAttribute::SALES_PRICE);
        
        /** @var VariationUnitAttribute $unitPart */
        $unitPart = app(VariationUnitAttribute::class);
        $unitPart->addLazyLoadParts(VariationUnitAttribute::UNIT);
        
        /** @var VariationImageAttribute $imagePart */
        $imagePart = app(VariationImageAttribute::class);
        
        /** @var VariationAttributeValueAttribute $attriuteValuePart */
        $attributeValuePart = app(VariationAttributeValueAttribute::class);
        $attributeValuePart->addLazyLoadParts(
            VariationAttributeValueAttribute::ATTRIBUTE,
            VariationAttributeValueAttribute::VALUE
        );
        
        return [
            $basePart,
            $pricePart,
            $unitPart,
            $imagePart,
            $attributeValuePart
        ];
    }
}
