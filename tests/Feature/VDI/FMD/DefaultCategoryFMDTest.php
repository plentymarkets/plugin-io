<?php

namespace IO\Tests\Feature\VDI\FMD;


use IO\Helper\VDIToElasticSearchMapper;
use IO\Tests\Asserts\IsEqualArrayStructure;
use IO\Tests\TestCase;

use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Models\CategoryBranch;
use Plenty\Modules\Item\VariationCategory\Models\VariationCategory;
use Plenty\Modules\Item\VariationDefaultCategory\Models\VariationDefaultCategory;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationDefaultCategoryAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;

class DefaultCategoryFMDTest extends TestCase
{
    protected function setUp()
    {
       parent::setUp();
    }

    /** @test */
    public function should_map_vdi_result_to_es_result()
    {
        $variation = factory(\Plenty\Modules\Item\Variation\Models\Variation::class)->states('withMain')->create();


        /** @var Category $category */
        $category = factory(Category::class)->create();

        $catBranch = factory(CategoryBranch::class)->create([
            'categoryId' => $category->id,
            'category1Id' => $category->id,
        ]);

        $varCat = factory(VariationCategory::class)->create([
            'variationId' => $variation->id,
            'categoryId' => $category->id,
        ]);

        $varDefCat = factory(VariationDefaultCategory::class)->create([
            'variationId' => $variation->id,
            'branchId' => $category->id,
        ]);

          /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationDefaultCategoryAttribute $defaultCategoryPart */
        $defaultCategoryPart = app(VariationDefaultCategoryAttribute::class);

        $defaultCategoryPart->addLazyLoadParts(
            VariationDefaultCategoryAttribute::CATEGORY
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $defaultCategoryPart]);

        $vdiContext
            ->setIds(collect($variation->id));

        $vdiResult = $vdi->getResult($vdiContext);

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ['defaultCategories.*']);

        $expectedStructure = [
            'documents' => [
                0 => [
                    'data' =>  [
                        'defaultCategories' => [
                                0 => [
                                  'id' => NULL,
                                  //'isNeckermannPrimary' => NULL,
                                  'level' => NULL,
                                  'linklist' => NULL,
                                  'manually' => NULL,
                                  'parentCategoryId' => NULL,
                                  'plentyId' => NULL,
                                  //'position' => NULL,
                                  'right' => NULL,
                                  'sitemap' => NULL,
                                  'type' => NULL,
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
