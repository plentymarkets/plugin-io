<?php

namespace IO\Helper;

use ClassesWithParents\F;

use IO\Services\VdiSearch\FMD\AttributeFMD;
use Plenty\Modules\Pim\MappingLayer\Models\BarcodeFMD;
use Plenty\Modules\Pim\MappingLayer\Models\DefaultCategoryFMD;
use Plenty\Modules\Pim\MappingLayer\Models\FieldMapDefinition;
use Plenty\Modules\Pim\MappingLayer\Models\ImageFMD;
use Plenty\Modules\Pim\MappingLayer\Models\ItemFMD;
use Plenty\Modules\Pim\MappingLayer\Models\PropertyFMD;
use Plenty\Modules\Pim\MappingLayer\Models\TextFMD;
use Plenty\Modules\Pim\MappingLayer\Models\UnitFMD;
use Plenty\Modules\Pim\MappingLayer\Models\VariationFMD;
use Plenty\Modules\Pim\MappingLayer\Models\VariationPropertyFMD;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceResultInterface;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class VDIToElasticSearchMapper
{
    //TODO add others
    private $fmdMap = [
        'attributes'          => AttributeFMD::class,
        'barcodes'            => BarcodeFMD::class,
        'defaultCategories'   => DefaultCategoryFMD::class,
        'images'              => ImageFMD::class,
        'item'                => ItemFMD::class,
        'properties'          => PropertyFMD::class,
        'texts'               => TextFMD::class,
        'unit'                => UnitFMD::class,
        'variation'           => VariationFMD::class,
        'variationProperties' => VariationPropertyFMD::class
    ];
    
    public function __construct(){}

    public function map(VariationDataInterfaceResultInterface $vdiResult, $resultFields = ['*'])
    {
        $data = [
            'documents' => [
                'score' => 0,
                'id' => 0,
                'data' => []
            ]
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
                $data['documents']['data'][$vdiKey] = [];
                foreach($fmdClasses as $fmdKey => $fmdClass)
                {
                    $data['documents']['data'][$vdiKey] = $fmdClass->fill($vdiVariation, $data['documents']['data'][$vdiKey], []);
                }
            }
        }

        return $data;
    }
}
