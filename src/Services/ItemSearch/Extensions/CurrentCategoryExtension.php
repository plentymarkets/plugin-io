<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\CategoryService;
use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Plugin\Application;

class CurrentCategoryExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch( $parentSearchBuilder )
    {
        return VariationSearchFactory::inherit(
            $parentSearchBuilder,
            [
                VariationSearchFactory::INHERIT_FILTERS,
                VariationSearchFactory::INHERIT_MUTATORS
            ])
            ->withResultFields([
                'item.id',
                'variation.id',
                'texts.*',
                'defaultCategories'
            ])
            ->build();
    }

    public function transformResult($baseResult, $extensionResult)
    {
        if ( count( $extensionResult['documents'] ) )
        {
            $data = $extensionResult['documents'][0]['data'];
            $defaultCategories = $data['defaultCategories'];

            if(count($defaultCategories))
            {
                $currentCategoryId = 0;
                foreach($defaultCategories as $defaultCategory)
                {
                    if((int)$defaultCategory['plentyId'] == pluginApp(Application::class)->getPlentyId())
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
                    $currentCategory = $categoryRepo->get($currentCategoryId, pluginApp(SessionStorageService::class)->getLang());

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