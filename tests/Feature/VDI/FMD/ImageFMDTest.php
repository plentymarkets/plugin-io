<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Models\CategoryBranch;
use Plenty\Modules\Item\ItemImage\Models\ItemImage;
use Plenty\Modules\Item\VariationCategory\Models\VariationCategory;
use Plenty\Modules\Item\VariationDefaultCategory\Models\VariationDefaultCategory;
use Plenty\Modules\Item\VariationImage\Models\VariationImage;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationDefaultCategoryAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class ImageCategoryFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
         if (!defined('SERVER_TYPE')) {
            define('SERVER_TYPE', "plenty_developer");
        }
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();

        $images = factory(ItemImage::class)->create(['itemId' => $variation->itemId]);
        $variationImages = factory(VariationImage::class)->create(['variationId' => $variation->id]);
          /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationImageAttribute $imagePart */
        $imagePart = app(VariationImageAttribute::class);
        $imagePart->addLazyLoadParts(VariationBaseAttribute::IMAGE);


         /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);

        $basePart->addLazyLoadParts(
            VariationBaseAttribute::IMAGE
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $basePart,
            $imagePart]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['images.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                          'images' => [
                                'all' =>
                                [
                                  0 =>
                                  [
                                    'attributeValueId' => NULL,
                                    'availabilities' =>
                                    [
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
                                    'names' =>
                                    [
                                      0 =>
                                      [
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
                                    'width' => NULL,
                                  ],
                                ],
                                'item' =>
                                [
                                  0 =>
                                  [
                                    'availabilities' =>
                                    [
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
                                    'names' =>
                                    [
                                      0 =>
                                      [
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
                                    'width' => NULL,
                                  ],
                                ],
                                'variation' =>
                                [
                                  0 =>
                                  [
                                    'attributeValueId' => NULL,
                                    'availabilities' =>
                                    [
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
                                    'names' =>
                                    [
                                      0 =>
                                      [
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
                                    'width' => NULL,
                                  ],
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
