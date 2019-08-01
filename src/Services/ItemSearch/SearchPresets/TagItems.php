<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Contracts\VariationSearchFactoryContract as VariationSearchFactory;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

/**
 * Class TagItems
 *
 * Search preset for tagged variations
 * Available options (see VariationList for inherited options)
 * - tagIds: List of tag ids to get assigned items for
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class TagItems extends VariationList
{
    public static function getSearchFactory($options)
    {
        $tagIds = [];
        if ( array_key_exists('tagIds', $options ) )
        {
            $tagIds = $options['tagIds'];
        }
        
        /** @var VariationSearchFactory $factory */
        $factory = parent::getSearchFactory($options)
            ->hasAnyTag($tagIds)
            ->groupByTemplateConfig()
            ->withGroupedAttributeValues()
            ->withReducedResults()
            ->withParts(self::getParts());

        return $factory;
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
