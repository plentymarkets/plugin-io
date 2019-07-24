<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Item\VariationDescription\Models\VariationDescription;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class TextFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {

        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();
        $variationDescription = factory(VariationDescription::class)->create( ['itemId' => $variation->itemId]);

        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);

        $basePart->addLazyLoadParts(
            VariationBaseAttribute::TEXTS
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $basePart]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['texts.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'texts' => [
                                0 => [
                                'description' => NULL,
                                'keywords' => NULL,
                                'lang' => NULL,
                                'metaDescription' => NULL,
                                'name1' => NULL,
                                'name2' => NULL,
                                'name3' => NULL,
                                'shortDescription' => NULL,
                                'technicalData' => NULL,
                                'urlPath' => NULL,
                            ]
                        ]
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
