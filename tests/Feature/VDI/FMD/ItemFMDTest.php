<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class ItemFMDTest extends TestCase
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

        $basePart->addLazyLoadParts(
            VariationBaseAttribute::ITEM
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
        $mappedData = $mappingHelper->map($vdiResult, ['item.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'item' =>   [
                            'add_cms_page' => NULL,
                            'ageRestriction' => NULL,
                            'amazonFbaPlatform' => NULL,
                            'amazonFedas' => NULL,
                            'amazonProductType' => NULL,
                            'condition' =>
                            [
                              'id' => NULL,
                              'names' =>
                              [
                                0 =>
                                [
                                  'lang' => NULL,
                                  'name' => NULL,
                                ],
                              ],
                            ],
                            'conditionApi' =>
                            [
                              'id' => NULL,
                              'names' =>
                              [
                                0 =>
                                [
                                  'lang' => NULL,
                                  'name' => NULL,
                                ],
                              ],
                            ],
                            'couponRestriction' => NULL,
                            'createdAt' => NULL,
                            'customsTariffNumber' => NULL,
                            'ebayCategory' => NULL,
                            'ebayCategory2' => NULL,
                            'ebayPresetId' => NULL,
                            'ebayStoreCategory' => NULL,
                            'ebayStoreCategory2' => NULL,
                            'feedback' => NULL,
                            //'feedbackCount' => NULL,
                            //'feedbackDecimal' => NULL,
                            'flags' =>
                            [
                              'flag1' =>
                              [
                                'icon' => NULL,
                                'id' => NULL,
                                'name' => NULL,
                                'text' => NULL,
                              ],
                              'flag2' =>
                              [
                                'icon' => NULL,
                                'id' => NULL,
                                'name' => NULL,
                                'text' => NULL,
                              ],
                            ],
                            //'gimahhot' => NULL,
                            'id' => NULL,
                            'isSerialNumber' => NULL,
                            'isShippableByAmazon' => NULL,
                            'isShippingPackage' => NULL,
                            'isSubscribable' => NULL,
                            'itemType' => NULL,
                            'mainVariationId' => NULL,
                            'manufacturer' =>
                            [
                              'externalName' => NULL,
                              //'icon' => NULL,
                              'id' => NULL,
                              'logo' => NULL,
                              'name' => NULL,
                              'position' => NULL,
                            ],
                            'manufacturerId' => NULL,
                            'maximumOrderQuantity' => NULL,
                            'ownerId' => NULL,
                            'position' => NULL,
                            'producingCountry' =>
                            [
                              'id' => NULL,
                              'isoCode2' => NULL,
                              'isoCode3' => NULL,
                              'name' => NULL,
                              'names' =>
                              [
                                0 =>
                                [
                                  'lang' => NULL,
                                  'name' => NULL,
                                ],
                              ],
                            ],
                            'producingCountryId' => NULL,
                            'rakutenCategoryId' => NULL,
                            'revenueAccount' => NULL,
                            'stockType' => NULL,
                            'storeSpecial' =>
                            [
                              'id' => NULL,
                              'names' =>
                              [
                                0 =>
                                [
                                  'lang' => NULL,
                                  'name' => NULL,
                                ],
                              ],
                            ],
                            //'type' => NULL,
                            'updatedAt' => NULL,
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
