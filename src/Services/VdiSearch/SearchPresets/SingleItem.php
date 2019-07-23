<?php

namespace IO\Services\VdiSearch\SearchPresets;

use IO\Services\VdiSearch\Factories\VariationSearchFactory;
//use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

/**
 * Class SingleItem
 *
 * Search preset for single items
 * Available options:
 * - itemId:        Item id to get
 * - variationId:   Variation id to get. If not defined, plugin configuration will be considered if main or child variation should be displayed.
 * - setCategory:   Flag indicating if item should be set as current item to be displayed in breadcrumbs
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class SingleItem implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        
        $searchFactory->withParts(self::getParts());

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withPropertyGroups()
            ->withOrderPropertySelectionValues()
            ->withVariationProperties()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->withBundleComponents()
            ->withAvailability()
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

        if(array_key_exists('variationId', $options) && $options['variationId'] != 0)
        {
            $searchFactory->hasVariationId($options['variationId']);
        }
        else
        {
            $templateConfigService = pluginApp( TemplateConfigService::class );
            $variationShowType = $templateConfigService->get('item.variation_show_type');
            if($variationShowType == 'main')
            {
                $searchFactory->isMain();
            }
            elseif($variationShowType == 'child')
            {
                $searchFactory->isChild();
            }
        }

        if ( array_key_exists( 'setCategory', $options ) && $options['setCategory'] === true )
        {
            $searchFactory->withCurrentCategory();
        }

        return $searchFactory;
    }
    
    private static function getParts()
    {
        /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);
        $basePart->addLazyLoadParts(
            VariationBaseAttribute::DESCRIPTION,
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