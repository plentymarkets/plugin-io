<?php

namespace IO\Services;

use IO\Extensions\Filters\ItemImagesFilter;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Webshop\Contracts\CategoryUrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\VariationUrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Plugin\Application;

class ItemSearchAutocompleteService
{
    /**
     * @inheritdoc
     */
    public function transformResult($itemSearchResult)
    {
        $newResult = [
            'item'        => $this->getItems($itemSearchResult['documents']),
            'category'    => $this->getCategories($itemSearchResult['categories.all']),
            'suggestion' => $this->getSuggestions([])
        ];

        return $newResult;
    }

    private function getItems($items)
    {
        $itemResult = [];
        if (count($items)) {
            /** @var SortingHelper $sortingHelper */
            $sortingHelper = pluginApp(SortingHelper::class);

            /** @var ItemImagesFilter $itemImageFilter */
            $itemImageFilter = pluginApp(ItemImagesFilter::class);

            /** @var VariationUrlBuilderRepositoryContract $variationUrlBuilderRepository */
            $variationUrlBuilderRepository = pluginApp(VariationUrlBuilderRepositoryContract::class);

            foreach ($items as $variation) {
                $itemId = $variation['data']['item']['id'];
                $variationId = $variation['data']['variation']['id'];

                $usedItemName = explode('.', $sortingHelper->getUsedItemName())[1];
                if (!strlen($usedItemName)) {
                    $usedItemName = 'name1';
                }

                $itemResult[] = $this->buildResult(
                    $variation['data']['texts'][$usedItemName],
                    $itemImageFilter->getFirstItemImageUrl(
                        $variation['data']['images'],
                        'urlPreview'
                    ),
                    $variationUrlBuilderRepository->buildUrl($itemId, $variationId)->append(
                        $variationUrlBuilderRepository->getSuffix($itemId, $variationId)
                    )->toRelativeUrl(),
                    '',
                    '',
                    0
                );
            }
        }

        return $itemResult;
    }

    private function getCategories($categories)
    {
        $categoryResult = [];

        /** @var CategoryRepositoryContract $categoryRepository */
        $categoryRepository = pluginApp(CategoryRepositoryContract::class);

        /** @var LocalizationRepositoryContract $localizationRepository */
        $localizationRepository = pluginApp(LocalizationRepositoryContract::class);

        /** @var Application $app */
        $app = pluginApp(Application::class);

        if (count($categories)) {
            foreach ($categories as $categoryId => $count) {
                if ((int)$categoryId > 0) {
                    /** @var Category $categoryData */
                    $categoryData = $categoryRepository->get($categoryId);

                    /** @var CategoryUrlBuilderRepositoryContract $categoryUrlRepository */
                    $categoryUrlBuilderRepository = pluginApp(CategoryUrlBuilderRepositoryContract::class);

                    $categoryResult[] = $this->buildResult(
                        $categoryData->details[0]->name,
                        $categoryData->details[0]->imagePath,
                        $categoryUrlBuilderRepository->buildUrl(
                            (int)$categoryId,
                            $localizationRepository->getLanguage(),
                            $app->getWebstoreId()
                        )->toRelativeUrl(),
                        '',
                        '',
                        $count
                    );
                }
            }
        }

        return $categoryResult;
    }

    private function getSuggestions($suggestions)
    {
        //TODO implement suggestion result
        return [];
    }

    private function buildResult($label, $image, $url, $beforeLabel, $afterLabel, $count)
    {
        return [
            'label' => $label,
            'image' => $image,
            'url' => $url,
            'beforeLabel' => $beforeLabel,
            'afterLabel' => $afterLabel,
            'count' => $count
        ];
    }
}
