<?php

namespace IO\Services;

use IO\Extensions\Filters\ItemImagesFilter;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Plugin\Application;

/**
 * Class ItemSearchAutocompleteService
 * @package IO\Services
 */
class ItemSearchAutocompleteService
{
    /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
    private $urlBuilderRepository;

    public function __construct(UrlBuilderRepositoryContract $urlBuilderRepository)
    {
        $this->urlBuilderRepository = $urlBuilderRepository;
    }

    /**
     * @param array $itemSearchResult
     * @return array
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

    /**
     * @param array $items
     * @return array
     */
    private function getItems($items)
    {
        $itemResult = [];
        if (count($items)) {
            /** @var SortingHelper $sortingHelper */
            $sortingHelper = pluginApp(SortingHelper::class);

            /** @var ItemImagesFilter $itemImageFilter */
            $itemImageFilter = pluginApp(ItemImagesFilter::class);

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
                    $this->urlBuilderRepository->buildVariationUrl($itemId, $variationId)->append(
                        $this->urlBuilderRepository->getSuffix($itemId, $variationId)
                    )->toRelativeUrl(),
                    '',
                    '',
                    0
                );
            }
        }

        return $itemResult;
    }

    /**
     * @param array $categories
     * @return array
     */
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

                    $categoryResult[] = $this->buildResult(
                        $categoryData->details[0]->name,
                        $categoryData->details[0]->imagePath,
                        $this->urlBuilderRepository->buildCategoryUrl(
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

    /**
     * @param array $suggestions
     * @return array
     */
    private function getSuggestions($suggestions)
    {
        //TODO implement suggestion result
        return [];
    }

    /**
     * @param string $label
     * @param string $image
     * @param string $url
     * @param string $beforeLabel
     * @param string $afterLabel
     * @param int $count
     * @return array
     */
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
