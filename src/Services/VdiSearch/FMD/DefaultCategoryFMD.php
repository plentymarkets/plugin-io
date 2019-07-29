<?php

namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\DocumentService\Models\Variation\DefaultCategory;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationDefaultCategoryAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class DefaultCategoryFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationDefaultCategoryAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationDefaultCategoryAttribute::CATEGORY
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'defaultCategories';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $variationDefaultCategories = $decoratedVariation->defaultCategories;

        $data = [];
        foreach ($variationDefaultCategories AS $variationDefaultCategory) {

            $categoryArray = $variationDefaultCategory->with()->defaultCategory;
            $categoryArray['linklist'] = ($categoryArray['linklist'] == 'Y') ? true : false;
            $categoryArray['sitemap'] = ($categoryArray['sitemap'] == 'Y') ? true : false;
            $categoryArray['manually'] = $variationDefaultCategory->manually == 'Y';
            $categoryArray['plentyId'] = $variationDefaultCategory->plentyId;
            unset($categoryArray['details'], $categoryArray['clients']);
            $data[] = $categoryArray->toArray();
        }

        $content['defaultCategories'] = $data;

        return $content;
    }
}
