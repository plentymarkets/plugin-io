<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Item\VariationDescription\Models\VariationDescription;
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
                        'item' => [
                                0 => [
                                    'availabilities' => [
                                      'listing' => NULL,
                                      'mandant' => NULL,
                                      'market' => NULL,
                                    ],
                                    'cleanImageName' => NULL,
                                    'createdAt' => NULL,
                                    'documentUploadPath' => NULL,
                                    'documentUploadPathPreview' => NULL,
                                    'documentUploadPreviewHeight' => NULL,
                                    'documentUploadPreviewWidth' => NULL,
                                    'fileType' => NULL,
                                    'height' => NULL,
                                    'id' => NULL,
                                    'itemId' => NULL,
                                    'md5Checksum' => NULL,
                                    'md5ChecksumOriginal' => NULL,
                                    'names' => [
                                      0 => [
                                        'alternate' => NULL,
                                        'imageId' => NULL,
                                        'lang' => NULL,
                                        'name' => NULL,
                                      ],
                                    ],
                                    'path' => NULL,
                                    'position' => NULL,
                                    'size' => NULL,
                                    'storageProviderId' => NULL,
                                    'type' => NULL,
                                    'updatedAt' => NULL,
                                    'url' => NULL,
                                    'urlMiddle' => NULL,
                                    'urlPreview' => NULL,
                                    'urlSecondPreview' => NULL,
                                    'width' => NULL
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
