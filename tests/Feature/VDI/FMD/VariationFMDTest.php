<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class VariationFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        //TODO Malsch muss ein Seeder für Availability bauen
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();

          /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);

        $basePart->addLazyLoadParts(
            VariationBaseAttribute::ITEM,
            VariationBaseAttribute::AVAILABILITY
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
        $mappedData = $mappingHelper->map($vdiResult, ['variation.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'variation' => [
                                    //'activeChildren' => NULL,
                                    'automaticClientVisibility' => NULL,
                                    'automaticListVisibility' => NULL,
                                    'availability' =>
                                    [
                                      'averageDays' => NULL,
                                      'createdAt' => NULL,
                                      'icon' => NULL,
                                      'id' => NULL,
                                      'names' =>
                                      [
                                        0 =>
                                        [
                                          'availabilityId' => NULL,
                                          'createdAt' => NULL,
                                          'id' => NULL,
                                          'lang' => NULL,
                                          'name' => NULL,
                                          'updatedAt' => NULL,
                                        ],
                                      ],
                                      'updatedAt' => NULL,
                                    ],
                                    'availabilityUpdatedAt' => NULL,
                                    'availableUntil' => NULL,
                                    'bundleType' => NULL,
                                    'categoryVariationId' => NULL,
                                    'clientVariationId' => NULL,
                                    'createdAt' => NULL,
                                    'customs' => NULL,
                                    'estimatedAvailableAt' => NULL,
                                    'externalId' => NULL,
                                    'extraShippingCharge1' => NULL,
                                    'extraShippingCharge2' => NULL,
                                    //'hasCalculatedBundleMovingAveragePrice' => NULL,
                                    //'hasCalculatedBundleNetWeight' => NULL,
                                    //'hasCalculatedBundlePurchasePrice' => NULL,
                                    //'hasCalculatedBundleWeight' => NULL,
                                    'heightMM' => NULL,
                                    'id' => NULL,
                                    'intervalOrderQuantity' => NULL,
                                    'isActive' => NULL,
                                    'isAvailableIfNetStockIsPositive' => NULL,
                                    'isHiddenInCategoryList' => NULL,
                                    'isInvisibleIfNetStockIsNotPositive' => NULL,
                                    'isInvisibleInListIfNetStockIsNotPositive' => NULL,
                                    'isMain' => NULL,
                                    'isUnavailableIfNetStockIsNotPositive' => NULL,
                                    'isVisibleIfNetStockIsPositive' => NULL,
                                    'isVisibleInListIfNetStockIsPositive' => NULL,
                                    'itemId' => NULL,
                                    'lengthMM' => NULL,
                                    'mainVariationId' => NULL,
                                    'mainWarehouseId' => NULL,
                                    'marketVariationId' => NULL,
                                    'maximumOrderQuantity' => NULL,
                                    'mayShowUnitPrice' => NULL,
                                    'minimumOrderQuantity' => NULL,
                                    'model' => NULL,
                                    'movingAveragePrice' => NULL,
                                    'name' => NULL,
                                    'number' => NULL,
                                    'operatingCosts' => NULL,
                                    'packingUnitTypeId' => NULL,
                                    'packingUnits' => NULL,
                                    'palletTypeId' => NULL,
                                    'parentVariationId' => NULL,
                                    'picking' => NULL,
                                    'position' => NULL,
                                    'priceCalculationId' => NULL,
                                    'propertyVariationId' => NULL,
                                    'purchasePrice' => NULL,
                                    'relatedUpdatedAt' => NULL,
                                    'releasedAt' => NULL,
                                    'salesPriceVariationId' => NULL,
                                    'singleItemCount' => NULL,
                                    'stockLimitation' => NULL,
                                    'storageCosts' => NULL,
                                    'supplierVariationId' => NULL,
                                    'tagVariationId' => NULL,
                                    'transportationCosts' => NULL,
                                    //'unitCombinationId' => NULL,
                                    'unitsContained' => NULL,
                                    'updatedAt' => NULL,
                                    'vatId' => NULL,
                                    'warehouseVariationId' => NULL,
                                    'weightG' => NULL,
                                    'weightNetG' => NULL,
                                    'widthMM' => NULL,
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
