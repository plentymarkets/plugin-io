<?php

namespace IO\Services;

use IO\Extensions\Filters\ItemImagesFilter;
use IO\Helper\Utils;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchSuggestions;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
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

    public function getResults($searchString, $searchTypes)
    {
        $searchFactories = [
            'items' => SearchItems::getSearchFactory(
                [
                    'query' => $searchString,
                    'autocomplete' => true,
                    'page' => 1,
                    'itemsPerPage' => 20,
                    'withCategories' => in_array('category', $searchTypes),
                ]
            )
        ];

        if (in_array('suggestion', $searchTypes)) {
            $searchFactories['suggestions'] = SearchSuggestions::getSearchFactory(
                [
                    'query' => $searchString,
                ]
            );
        }

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        $results = $itemSearchService->getResults($searchFactories);

        return $results;
    }

    /**
     * @param array $itemSearchResult
     * @return array
     */
    public function transformResult($itemSearchResult)
    {
        $newResult = [
            'item' => $this->getItems($itemSearchResult['items']['documents']),
            'category' => $this->getCategories($itemSearchResult['items']['categories.all']),
            'suggestion' => $this->getSuggestions($itemSearchResult['suggestions']['searchSuggestions'])
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
        if (is_array($items) && count($items)) {
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

                $defaultCategoryId = 0;
                if(count($variation['data']['defaultCategories'])) {
                    foreach ($variation['data']['defaultCategories'] as $defaultCategory) {
                        if ((int)$defaultCategory['plentyId'] == Utils::getPlentyId()) {
                            $defaultCategoryId = $defaultCategory['id'];
                        }
                    }
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
                    $this->getCategoryBranch($defaultCategoryId),
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

        if (is_array($categories) && count($categories)) {
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
                        $this->getCategoryBranch($categoryData->id),
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
        $suggestionResult = [];
        if (is_array($suggestions) && count($suggestions)) {
            foreach ($suggestions as $suggestion => $count) {
                $suggestionResult[] = $this->buildResult(
                    $suggestion,
                    '',
                    '',
                    '',
                    '',
                    $count
                );
            }
        }
        return $suggestionResult;
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

    /**
     * @param int $categoryId
     * @return string
     */
    private function getCategoryBranch($categoryId)
    {
        if($categoryId <= 0) {
            return '';
        }
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $category = $categoryService->get($categoryId);
        $branch = $category->branch->toArray();
        $result = [];

        for($i = 1; $i <= 6; $i++) {
            if(!is_null($branch["category{$i}Id"])) {
                $cat = $categoryService->get($branch["category{$i}Id"]);
                if(isset($cat->details[0])) {
                    $result[] = $cat->details[0]->name;
                }
            }
        }

        return implode(' » ', $result);
    }
}
