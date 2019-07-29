<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;
use IO\Tests\Unit\VariationSearchFactoryTest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPrice;
use Plenty\Modules\Item\VariationSalesPrice\Models\VariationSalesPrice;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class SalesPriceFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();
        $salesPrices = factory(VariationSalesPrice::class)->create(['variationId' => $variation->id]);

          /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

         /** @var VariationSalesPriceAttribute $salesPricePart */
        $salesPricePart = app(VariationSalesPriceAttribute::class);
        $salesPricePart->addLazyLoadParts(VariationSalesPriceAttribute::SALES_PRICE);

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $salesPricePart
        ]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['salesPrices.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'salesPrices' => [
                            0 => [
                              'createdAt' => NULL,
                              'id' => NULL,
                              'interval' => NULL,
                              'isCustomerPrice' => NULL,
                              'isDisplayedByDefault' => NULL,
                              'isLiveConversion' => NULL,
                              'minimumOrderQuantity' => NULL,
                              'names' => [
                                0 => [
                                  'createdAt' => NULL,
                                  'lang' => NULL,
                                  'nameExternal' => NULL,
                                  'nameInternal' => NULL,
                                  'priceId' => NULL,
                                  'updatedAt' => NULL,
                                ],
                              ],
                              'position' => NULL,
                              'price' => NULL,
                              'settings' => [
                                'clients' => NULL,
                                'countries' => NULL,
                                'currencies' => NULL,
                                'customerClasses' => NULL,
                                'referrers' => NULL,
                              ],
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
