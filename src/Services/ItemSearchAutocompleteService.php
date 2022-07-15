<?php

namespace IO\Services;

use IO\Extensions\Filters\ItemImagesFilter;
use IO\Helper\Utils;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchSuggestions;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

/**
 * Service Class ItemSearchAutocompleteService
 *
 * This service class contains functions for the autocompletion of the item search.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ItemSearchAutocompleteService
{
    /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
    private $urlBuilderRepository;

    /** @var LocalizationRepositoryContract $localizationRepository */
    private $localizationRepository;

    /** @var WebstoreConfiguration $webstoreConfiguration */
    private $webstoreConfiguration;

    /**
     * ItemSearchAutocompleteService constructor.
     * @param UrlBuilderRepositoryContract $urlBuilderRepository
     * @param LocalizationRepositoryContract $localizationRepository
     * @param WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
     */
    public function __construct(
        UrlBuilderRepositoryContract $urlBuilderRepository,
        LocalizationRepositoryContract $localizationRepository,
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
    )
    {
        $this->urlBuilderRepository = $urlBuilderRepository;
        $this->localizationRepository = $localizationRepository;
        $this->webstoreConfiguration = $webstoreConfigurationRepository->getWebstoreConfiguration();
    }

    /**
     * Gets a "Did you mean X?" string based on suggestions
     * @param string $searchString Original search string
     * @param array $suggestions Search suggestions based on search string
     * @return string
     */
    public function getDidYouMeanSuggestionSearchString($searchString, $suggestions)
    {
        if (is_array($suggestions)) {
            foreach ($suggestions['didYouMean'] as $suggestion) {
                $selectedSuggestion = $suggestion['suggestions'][0];
                if (count($suggestion['suggestions']) > 1) {
                    foreach ($suggestion['suggestions'] as $suggestionData) {
                        if ($suggestionData['score'] > $selectedSuggestion['score']) {
                            $selectedSuggestion = $suggestionData;
                        }
                    }
                }

                if (!is_null($selectedSuggestion['text']) && strlen($selectedSuggestion['text'])) {
                    $searchString = str_ireplace($suggestion['text'], $selectedSuggestion['text'], $searchString);
                }
            }
        }

        return $searchString;
    }

    /**
     * Get an item search result for the search string based on the chosen search types
     * @param string $searchString The search string
     * @param array $searchTypes What types of search to execute
     * @return array
     * @throws \Exception
     */
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
                    'searchOperator' => $this->webstoreConfiguration->itemAutocompleteSearchOperator
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
     * Transform the item search result into a flatter format
     * @param array $itemSearchResult Raw item search result
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

            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);

            $urlWithVariationId = $templateConfigService->getInteger(
                    'item.show_please_select'
                ) == 0 || $this->webstoreConfiguration->attributeSelectDefaultOption == 0;

            foreach ($items as $variation) {
                $itemId = $variation['data']['item']['id'];
                $variationId = $variation['data']['variation']['id'];

                $usedItemName = explode('.', $sortingHelper->getUsedItemName())[1];
                if (!strlen($usedItemName)) {
                    $usedItemName = 'name1';
                }

                $defaultCategoryId = 0;
                if (is_array($variation['data']['defaultCategories']) && count($variation['data']['defaultCategories'])) {
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
                    $this->urlBuilderRepository->buildVariationUrl(
                        $itemId,
                        $variationId,
                        $this->localizationRepository->getLanguage()
                    )->append(
                        $this->urlBuilderRepository->getSuffix($itemId, $variationId, $urlWithVariationId)
                    )->toRelativeUrl(
                        $this->localizationRepository->getLanguage() !== $this->webstoreConfiguration->defaultLanguage
                    ),
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

        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);

        if (is_array($categories) && count($categories)) {
            foreach ($categories as $categoryId => $count) {
                if ((int)$categoryId > 0) {
                    /** @var Category $categoryData */
                    $categoryData = $categoryService->get(
                        $categoryId,
                        $this->localizationRepository->getLanguage()
                    );

                    if (!is_null($categoryData) && $categoryService->isVisibleForWebstore(
                            $categoryData,
                            Utils::getWebstoreId(),
                            $this->localizationRepository->getLanguage()
                        )) {
                        $categoryResult[] = $this->buildResult(
                            $categoryData->details[0]->name,
                            $categoryData->details[0]->imagePath,
                            $this->urlBuilderRepository->buildCategoryUrl(
                                (int)$categoryId,
                                $this->localizationRepository->getLanguage(),
                                Utils::getWebstoreId()
                            )->toRelativeUrl(
                                $this->localizationRepository->getLanguage() !== $this->webstoreConfiguration->defaultLanguage
                            ),
                            $this->getCategoryBranch($categoryData->id),
                            '',
                            $count
                        );
                    }
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
        if ($categoryId <= 0) {
            return '';
        }

        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $category = $categoryService->get($categoryId);
        if (is_null($category) || is_null($category->branch)) {
            return '';
        }

        $branch = $category->branch->toArray();
        $result = [];

        for ($i = 1; $i <= 6; $i++) {
            if (!is_null($branch["category{$i}Id"])) {
                $cat = $categoryService->get($branch["category{$i}Id"]);
                if (isset($cat->details[0])) {
                    $result[] = $cat->details[0]->name;
                }
            }
        }

        return implode(' » ', $result);
    }
}
