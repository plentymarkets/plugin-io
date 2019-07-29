<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class AttributeFMDTest extends TestCase
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

         /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);

         /** @var VariationAttributeValueAttribute $attriuteValuePart */
        $attributeValuePart = app(VariationAttributeValueAttribute::class);
        $attributeValuePart->addLazyLoadParts(
            VariationAttributeValueAttribute::ATTRIBUTE,
            VariationAttributeValueAttribute::VALUE
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $basePart,
            $attributeValuePart
        ]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['attributes.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'attributes' => [
                          0 => [
                            'attributeId' => NULL,
                            'isLinkableToImage' => NULL,
                            'valueId' => NULL,
                            'attribute' => [
                              'isLinkableToImage' => NULL,
                              'neckermannAtEpAttribute' => NULL,
                              'laRedouteAttribute' => NULL,
                              'isSurchargePercental' => NULL,
                              'amazonAttribute' => NULL,
                              'pixmaniaAttribute' => NULL,
                              'typeOfSelectionInOnlineStore' => NULL,
                              'fruugoAttribute' => NULL,
                              'googleShoppingAttribute' => NULL,
                              'isGroupable' => NULL,
                              'names' => [
                                'attributeId' => NULL,
                                'name' => NULL,
                                'lang' => NULL,
                              ],
                              'backendName' => NULL,
                              'id' => NULL,
                              'position' => NULL,
                              'ottoAttribute' => NULL,
                              'updatedAt' => NULL,
                            ],
                            //'attributeValueSetId' => NULL,
                            'value' => [
                              'image' => NULL,
                              'percentageDistribution' => NULL,
                              'ottoValue' => NULL,
                              'laRedouteValue' => NULL,
                              'attributeId' => NULL,
                              'tracdelightValue' => NULL,
                              'amazonValue' => NULL,
                              'names' => [
                                'valueId' => NULL,
                                'name' => NULL,
                                'lang' => NULL,
                              ],
                              'backendName' => NULL,
                              'comment' => NULL,
                              'id' => NULL,
                              'position' => NULL,
                              'neckermannAtEpValue' => NULL,
                              'updatedAt' => NULL,
                            ],
                          ],
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
