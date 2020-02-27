<?php

namespace IO\Services\UrlBuilder;

use IO\Helper\StringUtils;
use IO\Helper\Utils;
use IO\Services\CategoryService;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\TemplateConfigService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Plugin\Log\Loggable;

/**
 * Class VariationUrlBuilder
 * @package IO\Services\UrlBuilder
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract
 */
class VariationUrlBuilder
{
    use Loggable;

    public static $urlPathMap;
    public static $requestedItems;

    /**
     * @param array $itemData
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract::fillItemUrl()
     */
    public static function fillItemUrl($itemData): void
    {
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];
        $defaultCategory = 0;
        if (count($itemData['defaultCategories'])) {
            $defaultCategory = $itemData['defaultCategories'][0]['id'];
        }

        $templateConfigService = pluginApp(TemplateConfigService::class);
        $usedItemName = $templateConfigService->get('item.name');
        if (strlen($usedItemName) <= 0) {
            $usedItemName = '0';
        }

        $itemNameFields = ['name1', 'name2', 'name3'];

        $usedName = $itemNameFields[$usedItemName];

        if (isset($itemData['texts']['lang']) && strlen($itemData['texts']['lang'])) {
            $lang = strtolower($itemData['texts']['lang']);
            self::$urlPathMap[$itemId][$variationId][$lang] = [
                'urlPath' => $itemData['texts']['urlPath'],
                'name' => $itemData['texts'][$usedName],
                'defaultCategory' => $defaultCategory
            ];
        } else {
            foreach ($itemData['texts'] as $lang => $texts) {
                $lang = strtolower($lang);
                self::$urlPathMap[$itemId][$variationId][$lang] = [
                    'urlPath' => $texts['urlPath'],
                    'name' => $texts[$usedName],
                    'defaultCategory' => $defaultCategory
                ];
            }
        }
    }

    /**
     * @param int $itemId
     * @param int $variationId
     * @param string|null $lang
     * @return UrlQuery
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract::buildVariationUrl()
     */
    public function buildUrl(int $itemId, int $variationId, string $lang = null): UrlQuery
    {
        $itemUrl = $this->buildUrlQuery(null, $lang);

        if ($lang === null) {
            $lang = Utils::getLang();
        }

        if (count(self::$urlPathMap[$itemId][$variationId]) <= 0) {
            $this->getLogger(__CLASS__)->debug(
                'IO::Debug.VariationUrlBuilder_searchItem',
                [
                    'itemId' => $itemId,
                    'variationId' => $variationId,
                    'lang' => $lang
                ]
            );

            $this->searchItem($itemId, $variationId, $lang);
        }

        $itemData = self::$urlPathMap[$itemId][$variationId][$lang];

        if (count($itemData)) {
            if (strlen($itemData['urlPath'])) {
                // url is set on item
                return $this->buildUrlQuery($itemData['urlPath'], $lang);
            }

            // generate url
            if (strlen($itemData['name'])) {
                $itemUrl = $this->generateUrlByConfig($itemData, $lang);

                if (strlen($itemUrl->getPath())) {
                    /** @var AuthHelper $authHelper */
                    $authHelper = pluginApp(AuthHelper::class);

                    /** @var LoggerContract $logger */
                    $logger = $this->getLogger(__CLASS__);

                    $authHelper->processUnguarded(
                        function () use ($variationId, $lang, $itemUrl, $logger) {
                            $logger->debug(
                                'IO::Debug.VariationUrlBuilder_saveItemUrl',
                                [
                                    'url' => rtrim($itemUrl->getPath(), '/'),
                                    'variationId' => $variationId,
                                    'lang' => $lang
                                ]
                            );

                            /** @var VariationDescriptionRepositoryContract $variationDescriptionRepository */
                            $variationDescriptionRepository = pluginApp(VariationDescriptionRepositoryContract::class);

                            $variationDescriptionRepository->update(
                                [
                                    'urlPath' => rtrim($itemUrl->getPath(), '/')
                                ],
                                $variationId,
                                $lang
                            );
                        }
                    );

                    self::$urlPathMap[$itemId][$variationId][$lang]['urlPath'] = $itemUrl->getPath();
                }
            }
        } else {
            $this->getLogger(__CLASS__)->error(
                'IO::Debug.VariationUrlBuilder_variationNotFound',
                [
                    'itemId' => $itemId,
                    'variationId' => $variationId,
                    'lang' => $lang
                ]
            );
        }

        return $itemUrl;
    }

    /**
     * @param int $itemId
     * @param int $variationId
     * @param bool $withVariationId
     * @return string
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract::getSuffix()
     */
    public function getSuffix($itemId, $variationId, $withVariationId = true): string
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $enableOldUrlPattern = $templateConfigService->getBoolean('global.enableOldUrlPattern');

        if ($withVariationId) {
            return $enableOldUrlPattern ? "/a-" . $itemId : "_" . $itemId . "_" . $variationId;
        }

        return $enableOldUrlPattern ? "/a-" . $itemId : "_" . $itemId;
    }

    /**
     * @param int $itemId
     * @param int $variationId
     * @param string $lang
     * @return array
     */
    private function searchItem($itemId, $variationId, $lang): array
    {
        if (!is_array(self::$requestedItems[$itemId][$variationId]) || !in_array(
                $lang,
                self::$requestedItems[$itemId][$variationId]
            )) {
            self::$requestedItems[$itemId][$variationId][] = $lang;

            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp(ItemSearchService::class);

            /** @var VariationSearchFactory $searchFactory */
            $searchFactory = pluginApp(VariationSearchFactory::class);
            $searchFactory
                ->withLanguage($lang)
                ->withUrls()
                ->hasItemId($itemId)
                ->hasVariationId($variationId);

            return $itemSearchService->getResults([$searchFactory])[0];
        }
        return [];
    }


    private function buildUrlQuery($path, $lang): UrlQuery
    {
        return pluginApp(
            UrlQuery::class,
            [
                'path' => $path,
                'lang' => $lang
            ]
        );
    }

    /**
     * @param array $itemData
     * @param string $lang
     * @return UrlQuery
     */
    private function generateUrlByConfig($itemData, $lang): UrlQuery
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);

        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);

        $urlPattern = $webstoreConfigurationRepository->getWebstoreConfiguration()->urlItemContent;
        if (!$templateConfigService->getBoolean('global.enableOldUrlPattern')) {
            $urlPattern = 'all';
        }

        $itemName4Url = StringUtils::string4URL($itemData['name']);

        if ($itemData['defaultCategory'] <= 0) {
            return $this->buildUrlQuery($itemName4Url, $lang);
        }

        switch ($urlPattern) {
            case 'all':
                // Fallback for variation based ceres-urls
                return $this->getBranchUrl($itemData['defaultCategory'], $lang, 6)->join($itemName4Url);

            case '':
                // Default config for legacy callisto shops
                // => /category_1/category_2/category_3/item_name
                return $this->getBranchUrl($itemData['defaultCategory'], $lang, 3)->join($itemName4Url);
            case 'cat1':
                // => /category_1/name
                return $this->getBranchUrl($itemData['defaultCategory'], $lang, 1)->join($itemName4Url);
            case 'cat0':
                // => /name
                return $this->buildUrlQuery($itemName4Url, $lang);
            case 'name_cat1':
                // => /name/category_1
                return $this->buildUrlQuery($itemName4Url, $lang)
                    ->join(
                        $this->getBranchUrl($itemData['defaultCategory'], $lang, 1)->getPath(false)
                    );
            case 'name_cat':
                // => /name/category_1/category_2/category_3
                return $this->buildUrlQuery($itemName4Url, $lang)
                    ->join(
                        $this->getBranchUrl($itemData['defaultCategory'], $lang, 3)->getPath(false)
                    );
            default:
                return $this->getBranchUrl($itemData['defaultCategory'], $lang, 6)->join($itemName4Url);
        }
    }

    /**
     * @param int $categoryId
     * @param string $lang
     * @param int $maxLevel
     * @return UrlQuery
     */
    private function getBranchUrl($categoryId, $lang, $maxLevel = 6): UrlQuery
    {
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $category = $categoryService->get($categoryId);

        if (!is_null($category)) {
            /** @var CategoryUrlBuilder $categoryUrlBuilder */
            $categoryUrlBuilder = pluginApp(CategoryUrlBuilder::class);

            if (!is_null($category->branch)) {
                $branch = $category->branch->toArray();
                for ($i = $maxLevel; $i > 0; $i--) {
                    if (!is_null($branch['category' . $i . 'Id']) && $branch['category' . $i . 'Id'] > 0) {
                        return $categoryUrlBuilder->buildUrl(
                            $branch['category' . $i . 'Id'],
                            $lang
                        );
                    }
                }
            } else {
                $this->getLogger(__CLASS__)->error(
                    'IO::Debug.VariationUrlBuilder_noCategoryBranch',
                    [
                        'categoryId' => $categoryId,
                        'lang' => $lang
                    ]
                );
            }

            /** @var CategoryUrlBuilder $categoryUrlBuilder */
            $categoryUrlBuilder = pluginApp(CategoryUrlBuilder::class);

            return $categoryUrlBuilder->buildUrl($categoryId, $lang);
        } else {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.VariationUrlBuilder_categoryNotFound',
                [
                    'categoryId' => $categoryId,
                    'lang' => $lang
                ]
            );
        }

        return $this->buildUrlQuery('', $lang);
    }
}
