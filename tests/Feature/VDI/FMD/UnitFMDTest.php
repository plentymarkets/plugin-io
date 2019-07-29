<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Modules\Tag\Models\Tag;
use Plenty\Modules\Tag\Models\TagRelationship;

class UnitFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();

        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationUnitAttribute $unitPart */
        $unitPart = app(VariationUnitAttribute::class);

        $unitPart->addLazyLoadParts(
            VariationUnitAttribute::UNIT
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $unitPart]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['unit.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'unit' => [
                            'content' => NULL,
                            'createdAt' => NULL,
                            'id' => NULL,
                            'isDecimalPlacesAllowed' => NULL,
                            'names' => [
                              0 => [
                                'lang' => NULL,
                                'name' => NULL,
                                'unitId' => NULL,
                              ],
                            ],
                            'position' => NULL,
                            'unitOfMeasurement' => NULL,
                            'updatedAt' => NULL,
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
