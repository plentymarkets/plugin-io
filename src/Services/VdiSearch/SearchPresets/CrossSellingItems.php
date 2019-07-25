<?php

namespace IO\Services\VdiSearch\SearchPresets;

use IO\Services\CategoryService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\SortingHelper;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

/**
 * Class CrossSellingItems
 *
 * Search preset for cross selling items
 * Available options:
 * - itemId:    Id of the item to get cross selling items for
 * - relation:  The relation to consider when getting cross selling items
 *
 * @package IO\Services\VdiSearch\SearchPresets
 */
class CrossSellingItems implements SearchPreset
{
    /**
     * @inheritdoc
     */
    public static function getSearchFactory($options)
    {
        $itemId = $options['itemId'];
        $relation = $options['relation'];
        $sorting = $options['sorting'];


        if(!isset($itemId) || !strlen($itemId))
        {
            $categoryService = pluginApp(CategoryService::class);
            $currentItem = $categoryService->getCurrentItem();
            $itemId = $currentItem['item']['id'] ?? 0;
        }
        /** @var ItemCrossSellingService $crossSellingService */
        $crossSellingService = pluginApp( ItemCrossSellingService::class );

        if ( $relation === null )
        {
            $relation = $crossSellingService->getType();
        }

        if(is_null($sorting))
        {
            $sorting = SortingHelper::splitPathAndOrder($crossSellingService->getSorting());
        }elseif(strlen($sorting))
        {
            $sorting = SortingHelper::getSorting($sorting);
        }


        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )->withParts( self::getParts() );

        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withImages()
            ->withPrices()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->groupByTemplateConfig()
            ->isCrossSellingItem( $itemId, $relation )
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->sortBy($sorting['path'], $sorting['order'])
            ->withLinkToContent()
            ->withGroupedAttributeValues()
            ->withReducedResults();

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
