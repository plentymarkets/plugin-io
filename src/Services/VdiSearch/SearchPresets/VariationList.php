<?php

namespace IO\Services\VdiSearch\SearchPresets;

use IO\Services\VdiSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\SortingHelper;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

/**
 * Class VariationList
 *
 * Search preset for variation lists.
 * Available options:
 * - variationIds:      List of variations to receive.
 * - sorting:           Configuration value to get sorting for.
 * - sortingField:      Field to sort items by. Will be appended to sorting list if sorting configuration is defined.
 * - sortingOrder:      Order to sort items with ('asc', 'desc')
 * - page:              The current page
 * - itemsPerPage:      Number of items per page
 * - excludeFromCache:  Set to true if results should not be linked to response
 *
 * @package IO\Services\VdiSearch\SearchPresets
 */
class VariationList implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $variationIds = [];
        if ( array_key_exists('variationIds', $options ) )
        {
            $variationIds = $options['variationIds'];
        }

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );

        $searchFactory->withParts( self::getParts() );

        $searchFactory
            ->withImages()
            ->withPrices()
            ->withUrls()
            ->withLanguage()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->hasPriceForCustomer()
            ->withReducedResults();

        if ( !array_key_exists('excludeFromCache', $options) || $options['excludeFromCache'] === false )
        {
            $searchFactory->withLinkToContent();
        }

        if ( count( $variationIds ) )
        {
            $searchFactory->hasVariationIds( $variationIds );
        }

        if ( array_key_exists( 'sorting', $options ) )
        {
            if ( $options['sorting'] === null )
            {
                $searchFactory->setOrder( $variationIds );
            }
            else
            {
                $sorting = SortingHelper::getSearchSorting( $options['sorting'] );
                $searchFactory->sortByMultiple( $sorting );
            }
        }

        if ( array_key_exists('sortingField', $options ) && $options['sortingField'] !== null )
        {
            $searchFactory->sortBy( $options['sortingField'], $options['sortingOrder'] );
        }

        if ( array_key_exists('page', $options) && $options['page'] !== null
            && array_key_exists('itemsPerPage', $options ) && $options['itemsPerPage'] !== null )
        {
            $searchFactory->setPage( $options['page'], $options['itemsPerPage'] );
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
