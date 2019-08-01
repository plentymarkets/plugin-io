<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Contracts\FacetSearchFactoryContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

/**
 * Class Facets
 *
 * Search preset for facets.
 * Available options:
 * - facets:        Values of active facets.
 * - categoryId:    Category Id to filter variations by.
 * - query:         Search string to get variations by.
 * - autocomplete:  Flag indicating if autocomplete search should be used (boolean). Will only be used if 'query' is defined.
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class Facets implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var FacetSearchFactoryContract $searchFactory */
        $searchFactory = pluginApp(FacetSearchFactoryContract::class)->create( $options['facets'] );
        $searchFactory
            ->withMinimumCount()
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList( false )
            ->groupByTemplateConfig()
            ->hasNameInLanguage()
            ->setPage(1,0)
            ->hasPriceForCustomer();

        if ( array_key_exists('categoryId', $options ) && (int)$options['categoryId'] > 0 )
        {
            $searchFactory->isInCategory( $options['categoryId'] );
        }

        if ( array_key_exists('query', $options) && strlen($options['query'] ) )
        {
            if ( array_key_exists('autocomplete', $options ) && $options['autocomplete'] === true )
            {
                $searchFactory->hasNameString( $options['query'] );
            }
            else
            {
                $searchFactory->hasSearchString( $options['query'] );
            }
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
