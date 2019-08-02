<?php

namespace IO\Helper;

use IO\Services\VdiSearch\FMD\AttributeFMD;
use IO\Services\VdiSearch\FMD\BarcodeFMD;
use IO\Services\VdiSearch\FMD\CrossSellingFMD;
use IO\Services\VdiSearch\FMD\DefaultCategoryFMD;
use IO\Services\VdiSearch\FMD\FilterFMD;
use IO\Services\VdiSearch\FMD\ImageFMD;
use IO\Services\VdiSearch\FMD\ItemFMD;
use IO\Services\VdiSearch\FMD\PropertyFMD;
use IO\Services\VdiSearch\FMD\SalesPriceFMD;
use IO\Services\VdiSearch\FMD\SkusFMD;
use IO\Services\VdiSearch\FMD\StockFMD;
use IO\Services\VdiSearch\FMD\TagsFMD;
use IO\Services\VdiSearch\FMD\TextFMD;
use IO\Services\VdiSearch\FMD\UnitFMD;
use IO\Services\VdiSearch\FMD\VariationFMD;
use IO\Services\VdiSearch\FMD\VariationPropertyFMD;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBarcodeAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationDefaultCategoryAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSkuAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;

class VDIPart
{

    /**
     * VDIPart constructor.
     */
    public function __construct(){}

    private $partMaps = [
        'attributes'          => [
            VariationAttributeValueAttribute::class => AttributeFMD::class
        ],
        'barcodes'            => [
            VariationBarcodeAttribute::class => BarcodeFMD::class
        ],
        'crossSelling'        => [
            VariationBaseAttribute::class => CrossSellingFMD::class
        ],
        'defaultCategories'   => [
            VariationDefaultCategoryAttribute::class => DefaultCategoryFMD::class,
        ],
        'filter'              => [
            VariationBaseAttribute::class => FilterFMD::class
        ],
        'images'              => [
            VariationImageAttribute::class => ImageFMD::class,
        ],
        'item'                => [
            VariationBaseAttribute::class => ItemFMD::class
        ],
        'properties'          => [
            VariationBaseAttribute::class => PropertyFMD::class
        ],
        'salesPrices'         => [
            VariationSalesPriceAttribute::class => SalesPriceFMD::class
        ],
        'skus'                => [
            VariationSkuAttribute::class => SkusFMD::class
        ],
        'stock'               => [
            VariationBaseAttribute::class => StockFMD::class
        ],
        'tags'                => [
            VariationBaseAttribute::class => TagsFMD::class
        ],
        'texts'               => [
            VariationBaseAttribute::class => TextFMD::class
        ],
        'unit'                => [
            VariationUnitAttribute::class => UnitFMD::class
        ],
        'variation'           => [
            VariationBaseAttribute::class => VariationFMD::class
        ],
        'variationProperties' => [
            VariationBaseAttribute::class => VariationPropertyFMD::class
        ]
    ];


    /**
     * @param array $resultFields
     * @return array
     */
    public function getPartsByResultFields($resultFields = [])
    {
        $parts = [];
        if(count($resultFields) === 0 || in_array('*', $resultFields) )
        {
            foreach($this->partMaps as $partConfig)
            {
                $partName = key($partConfig);
                if(isset($parts[$partName]))
                {
                    $part = $parts[$partName];
                    $fmd = pluginApp($partConfig[$partName]);
                    $part->addLazyLoadParts(...$fmd->getLazyLoadable());
                    $parts[$partName] = $part;
                }
                else
                {
                    $part = app($partName);
                    $fmd = pluginApp($partConfig[$partName]);
                    $part->addLazyLoadParts(...$fmd->getLazyLoadable());
                    $parts[$partName] = $part;
                }
            }
        }
        else
        {
            foreach($resultFields as $resultField) {
                $key = explode('.', $resultField);
                $partConfig = $this->partMaps[$key[0]];
                if($partConfig === null)
                {
                    continue;
                }
                $partName = key($partConfig);
                if (isset($parts[$partName]))
                {
                    $part = $parts[$partName];
                    $fmd = pluginApp($partConfig[$partName]);
                    $part->addLazyLoadParts(...$fmd->getLazyLoadable());
                    $parts[$partName] = $part;
                }
                else
                {
                    $part = app($partName);
                    $fmd = pluginApp($partConfig[$partName]);
                    $part->addLazyLoadParts(...$fmd->getLazyLoadable());
                    $parts[$partName] = $part;
                }

             }
        }
            $parts = array_values($parts);
        return $parts;
    }
}
