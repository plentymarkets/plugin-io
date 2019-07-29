<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSkuAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class SkusFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();

        $skus = factory(VariationSku::class)->create(['variationId' => $variation->id]);

        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationSkuAttribute $skuPart */
        $skuPart = app(VariationSkuAttribute::class);

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $skuPart]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['skus.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                         'skus' => [
                            0 => [
                              'accountId' => NULL,
                              'additionalInformation' => NULL,
                              //'createdAt' => NULL,
                              'deletedAt' => NULL,
                              'exportedAt' => NULL,
                              //'id' => NULL,
                              'initialSku' => NULL,
                              'isActive' => NULL,
                              'marketId' => NULL,
                              'parentSku' => NULL,
                              'sku' => NULL,
                              'status' => NULL,
                              'stockUpdatedAt' => NULL,
                              //'variationId' => NULL
                            ]
                         ]
                    ],
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
