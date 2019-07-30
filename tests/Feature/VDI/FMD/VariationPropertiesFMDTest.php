<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Services\VdiSearch\FMD\VariationPropertyFMD;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Item\Property\Models\Property;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationProperty\Models\VariationPropertyValue;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSkuAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Modules\Property\Models\PropertyGroup;
use Plenty\Modules\Property\Models\PropertyGroupName;
use Plenty\Modules\Property\Models\PropertyGroupRelation;
use Plenty\Modules\Property\Models\PropertyName;
use Plenty\Modules\Property\Models\PropertyRelation;

class VariationPropertiesFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        /** @var Variation $variation */
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();

        /** @var PropertyRelation $property */
        $property = factory(PropertyRelation::class)->create([
            'relationTargetId' => $variation->propertyVariationId,
            'relationTypeIdentifier' => 'item'
        ]);

        $propertyId = $property->propertyId;

        /** @var PropertyGroup $group */
        $group = factory(PropertyGroup::class)->create();

        $names = factory(PropertyGroupName::class)->create([
            'lang'            => 'en',
            'name'            => 'english',
            'propertyGroupId' => $group->id]
        );

        //  $group->names()->create([
        //    'lang'            => 'en',
        //    'name'            => 'english',
        //    'propertyGroupId' => $group->id]);

        PropertyGroupRelation::attachGroup($propertyId, $group->id);

        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);

        $basePart->addLazyLoadParts(
            VariationBaseAttribute::PROPERTY
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
        $mappedData = $mappingHelper->map($vdiResult, ['variationProperties.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                         'variationProperties' => [
                                0 => [
                                  'id' => NULL,
                                  'property' => [
                                    'cast' => NULL,
                                    'clients' => NULL,
                                    'display' => NULL,
                                    'groups' => [
                                      0 => [
                                        'id' => NULL,
                                        'names' => [
                                          0 => [
                                            'description' => NULL,
                                            'lang' => NULL,
                                            'name' => NULL,
                                          ],
                                        ],
                                        'position' => NULL,
                                      ],
                                    ],
                                    'id' => NULL,
                                    'names' => [
                                      0 => [
                                        'description' => NULL,
                                        'lang' => NULL,
                                        'name' => NULL,
                                        'value' => NULL,
                                      ],
                                    ],
                                    'options' => NULL,
                                    'position' => NULL,
                                    'referrer' => NULL,
                                  ],
                                  'values' => [
                                    0 => [
                                      'description' => NULL,
                                      'lang' => NULL,
                                      'value' => NULL,
                                    ],
                                  ],
                                ],
                              ],
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
