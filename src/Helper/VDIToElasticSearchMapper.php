<?php

namespace IO\Helper;


use IO\Services\VdiSearch\FMD\AttributeFMD;
use IO\Services\VdiSearch\FMD\BarcodeFMD;
use IO\Services\VdiSearch\FMD\CrossSellingFMD;
use IO\Services\VdiSearch\FMD\DefaultCategoryFMD;
use IO\Services\VdiSearch\FMD\ImageFMD;
use IO\Services\VdiSearch\FMD\PropertyFMD;
use IO\Services\VdiSearch\FMD\SalesPriceFMD;
use IO\Services\VdiSearch\FMD\SkusFMD;
use IO\Services\VdiSearch\FMD\StockFMD;
use IO\Services\VdiSearch\FMD\TagsFMD;
use IO\Services\VdiSearch\FMD\TextFMD;
use IO\Services\VdiSearch\FMD\UnitFMD;
use IO\Services\VdiSearch\FMD\VariationFMD;
use IO\Services\VdiSearch\FMD\ItemFMD;
use IO\Services\VdiSearch\FMD\VariationPropertyFMD;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceResultInterface;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class VDIToElasticSearchMapper
{
    //TODO add others
    private $fmdMap = [
        'attributes'          => AttributeFMD::class,
        'barcodes'            => BarcodeFMD::class,
        'crossSelling'        => CrossSellingFMD::class,
        'defaultCategories'   => DefaultCategoryFMD::class,
        'images'              => ImageFMD::class,
        'item'                => ItemFMD::class,
        'properties'          => PropertyFMD::class,
        'salesPrices'         => SalesPriceFMD::class,
        'skus'                => SkusFMD::class,
        'stock'               => StockFMD::class,
        'tags'                => TagsFMD::class,
        'texts'               => TextFMD::class,
        'unit'                => UnitFMD::class,
        'variation'           => VariationFMD::class,
        'variationProperties' => VariationPropertyFMD::class
    ];

    public function __construct(){}

    public function map(VariationDataInterfaceResultInterface $vdiResult, $resultFields = ['*'])
    {
        $data = [
            'documents' => []
        ];

        $fmdClasses = [];
        if(count($resultFields))
        {
            foreach($resultFields as $resultField)
            {
                $e = explode('.', $resultField);
                $resultFieldMainKey = $e[0];
                if(array_key_exists($resultFieldMainKey, $this->fmdMap) && !array_key_exists($resultFieldMainKey, $fmdClasses))
                {
                    $fmdClasses[$resultFieldMainKey] = app($this->fmdMap[$resultFieldMainKey]);
                }
            }
        }

        if(count($fmdClasses))
        {
            /**
             * @var Variation $vdiVariation
             */
            foreach($vdiResult->get() as $vdiKey => $vdiVariation)
            {
                $item = [
                    'score' => 0,
                    'id' => $vdiVariation->id,
                    'data' => []];
                foreach($fmdClasses as $fmdKey => $fmdClass)
                {
                    $item['data'] = $fmdClass->fill($vdiVariation, $item['data'], []);
                }
                $data['documents'][] = $item;
            }
        }

        return $data;
    }
}
