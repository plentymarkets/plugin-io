<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Item\VariationBarcode\Models\VariationBarcode;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBarcodeAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class BarcodeFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();
        $barcode = factory(VariationBarcode::class)->create(['variationId' => $variation->id]);

          /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationBarcodeAttribute $barcodePart */
        $barcodePart = app(VariationBarcodeAttribute::class);

        $barcodePart->addLazyLoadParts(
            VariationBarcodeAttribute::BARCODE
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $barcodePart]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['barcodes.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'barcodes' => [
                            0 => [
                              'code' => NULL,
                              'createdAt' => NULL,
                              'id' => NULL,
                              'name' => NULL,
                              //'referrers' => NULL,
                              'type' => NULL,
                              'updatedAt' => NULL,
                            ],
                          ],
                        ]
                    ]
                ]
        ];

        try
        {
            $this->assertTrue(IsEqualArrayStructure::validate($mappedData, $expectedStructure));
        } catch(\Exception $exception)
        {
            $this->fail("Code " . $exception->getCode() . ", Message: " . $exception->getMessage());
        }
    }

}
