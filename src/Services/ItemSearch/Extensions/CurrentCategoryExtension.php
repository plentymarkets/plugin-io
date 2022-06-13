<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Helper\Utils;
use IO\Services\CategoryService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class CurrentCategoryExtension
 *
 * Set current category when loading an item to be displayed in breadcrumbs or navigation templates.
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class CurrentCategoryExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch( $parentSearchBuilder )
    {
        return $parentSearchBuilder->inherit(
            [
                VariationSearchFactory::INHERIT_FILTERS,
                VariationSearchFactory::INHERIT_MUTATORS,
                VariationSearchFactory::INHERIT_PAGINATION,
                VariationSearchFactory::INHERIT_COLLAPSE,
                VariationSearchFactory::INHERIT_AGGREGATIONS,
                VariationSearchFactory::INHERIT_SORTING
            ])
            ->withResultFields([
                'item.id',
                'variation.id',
                'texts.*',
                'defaultCategories'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        if ( count( $extensionResult['documents'] ) )
        {
            $data = $extensionResult['documents'][0]['data'];
            $defaultCategories = $data['defaultCategories'];

            if(is_array($defaultCategories) && count($defaultCategories))
            {
                $currentCategoryId = 0;
                foreach($defaultCategories as $defaultCategory)
                {
                    if((int)$defaultCategory['plentyId'] == Utils::getPlentyId())
                    {
                        $currentCategoryId = $defaultCategory['id'];
                    }
                }
                if((int)$currentCategoryId > 0)
                {
                    /**
                     * @var CategoryRepositoryContract $categoryRepo
                     */
                    $categoryRepo = pluginApp(CategoryRepositoryContract::class);
                    $currentCategory = $categoryRepo->get($currentCategoryId, Utils::getLang());

                    /**
                     * @var CategoryService $categoryService
                     */
                    $categoryService = pluginApp(CategoryService::class);
                    $categoryService->setCurrentCategory($currentCategory);
                    $categoryService->setCurrentItem($data);
                }
            }
        }

        return $baseResult;
    }
}
