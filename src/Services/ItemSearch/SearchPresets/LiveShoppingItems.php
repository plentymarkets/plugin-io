<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Contracts\VariationSearchFactoryContract as VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;


class LiveShoppingItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        
        if(isset($options['resultFields']) && count($options['resultFields']))
        {
            $searchFactory
                ->withResultFields($options['resultFields'])
                ->withParts(self::getParts());
        }
        else
        {
            $searchFactory->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
            )
            ->withParts(self::getParts());
        }
        
        
        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withPropertyGroups()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->withBundleComponents()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->withLinkToContent()
            ->withReducedResults();
        
        if(array_key_exists('itemId', $options) && $options['itemId'] != 0)
        {
            $searchFactory->hasItemId($options['itemId']);
        }
    
        if(array_key_exists('itemIds', $options) && count($options['itemIds']))
        {
            $searchFactory->hasItemIds($options['itemIds']);
        }
    
        if(array_key_exists('sorting', $options) && count($options['sorting']))
        {
            $searchFactory->sortBy($options['sorting']['path'], $options['sorting']['order']);
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
