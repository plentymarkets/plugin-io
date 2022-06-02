<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Helper\Utils;
use IO\Services\CategoryService;
use IO\Services\ItemListService;
use IO\Services\ItemSearch\Factories\VariationSearchResultFactory;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\CategoryItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SingleItem;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationAttributeMap;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemController
 *
 * @package IO\Controllers
 */
class ItemController extends LayoutController
{
    use Loggable;
    private $plentyId;

    /**
     * Prepare and render the item data.
     * @param string $slug
     * @param int $itemId The itemId read from current request url. Will be null if item url does not contain a slug.
     * @param int $variationId
     * @param Category $category
     * @return string
     * @throws \ErrorException
     */
    public function showItem(
        string $slug = "",
        int $itemId = 0,
        int $variationId = 0,
        $category = null
    ) {
        $itemSearchOptions = [
            'itemId' => $itemId,
            'variationId' => $variationId,
            'setCategory' => is_null($category)
        ];

        $this->plentyId = Utils::getPlentyId();

        $searches = [
            'item' => SingleItem::getSearchFactory($itemSearchOptions),
            'variationAttributeMap' => VariationAttributeMap::getSearchFactory($itemSearchOptions)
        ];

        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);

        if ($variationId > 0 && $templateConfigService->getInteger('item.show_please_select') == 1) {
            unset($itemSearchOptions['variationId']);
            $searches['dynamic'] = SingleItem::getSearchFactory($itemSearchOptions);
        }

        /** @var ItemSearchService $itemSearchService */

        $itemSearchService = pluginApp(ItemSearchService::class);
        $itemResult = $itemSearchService->getResults($searches);

        if (!is_null($category)) {
            /** @var CategoryService $categoryService */
            $categoryService = pluginApp(CategoryService::class);
            $categoryService->setCurrentCategory($category);
        }

        if (isset($itemResult['item']['documents'][0]['data']['currentData'])) {
            /** @var CategoryService $categoryService */
            $categoryService = pluginApp(CategoryService::class);
            if (is_null($category) && isset($itemResult['item']['documents'][0]['data']['currentData']['category'])) {
                $categoryService->setCurrentCategory(
                    $itemResult['item']['documents'][0]['data']['currentData']['category']
                );
            }
            if (isset($itemResult['item']['documents'][0]['data']['currentData']['item'])) {
                $categoryService->setCurrentItem($itemResult['item']['documents'][0]['data']['currentData']['item']);
            }

            if (isset($itemResult['item']['documents'][0]['data']['currentData']['setComponents'])) {
                $itemResult['setComponents'] = $itemResult['item']['documents'][0]['data']['currentData']['setComponents'];
            }

            if (isset($itemResult['item']['documents'][0]['data']['currentData']['setAttributeMap'])) {
                $itemResult['setAttributeMap'] = $itemResult['item']['documents'][0]['data']['currentData']['setAttributeMap'];
            }

            unset($itemResult['item']['documents'][0]['data']['currentData']);
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        $defaultCategories = $itemResult['item']['documents'][0]['data']['defaultCategories'] ?? [];
        $defaultCategory = array_filter(
            $defaultCategories,
            function ($category) {
                return $category['plentyId'] == $this->plentyId;
            }
        );

        $shopBuilderRequest->setMainCategory($defaultCategory[0]['id']);
        $shopBuilderRequest->setMainContentType('singleitem');
        $itemResult['isItemSet'] = false;

        if ($itemResult['item']['documents'][0]['data']['item']['itemType'] === 'set' || $shopBuilderRequest->getPreviewContentType() === 'itemset') {
            $shopBuilderRequest->setMainContentType('itemset');
            $itemResult['isItemSet'] = true;
        }

        if ($shopBuilderRequest->isShopBuilder()) {
            /** @var VariationSearchResultFactory $searchResultFactory */
            $searchResultFactory = pluginApp(VariationSearchResultFactory::class);
            $itemResult['item'] = $searchResultFactory->fillSearchResults(
                $itemResult['item'],
                ResultFieldTemplate::get(ResultFieldTemplate::TEMPLATE_SINGLE_ITEM)
            );

            // variation attribute map does not contain initial variation if it is completely faked for the preview.
            // need to filter null values from variation list to avoid errors in the frontend
            $hasInitialVariation = false;
            $itemResult['variationAttributeMap']['variations'] = array_filter(
                $itemResult['variationAttributeMap']['variations'] ?? [],
                function ($variation) use ($itemResult) {
                    if(!empty($variation)) {
                        if($variation['variationId'] === $itemResult['documents'][0]['data']['variation']['id']) {
                            $hasInitialVariation = true;
                        }
                        return true;
                    }

                    return false;
                }
            );

            if(!$hasInitialVariation && count($itemResult['variationAttributeMap']['variations'])) {
                // fake entry in the variation attribute make for the faked initial variation
                $firstVariation = $itemResult['variationAttributeMap']['variations'][0];
                $itemResult['variationAttributeMap']['variations'][] = [
                    'variationId' => $itemResult['documents'][0]['data']['variation']['id'],
                    'isSalable' => false,
                    'unitCombinationId' => $firstVariation['unitCombinationId'],
                    'unitId' => $firstVariation['unitId'],
                    'unitName' => $firstVariation['unitName'],
                    'attributes' => []
                ];
            }

            if($shopBuilderRequest->getPreviewContentType() === 'itemset')
            {
                $previewSetComponentId = $itemResult['item']['documents'][0]['data']['setComponentVariationIds'][0];
                $previewSetComponent = $itemResult['setComponents'][0] ?? [
                        'variation' => [
                            'id' => $previewSetComponentId
                        ]
                    ];

                $itemResult['setComponents'] = [];
                $itemResult['setComponents'][] = $searchResultFactory->fillSearchResults(
                    [
                        'documents' => [
                            ['data' => $previewSetComponent]
                        ]
                    ]
                )['documents'][0]['data'];
            }
        }

        if (empty($itemResult['item']['documents'])) {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.ItemController_itemNotFound",
                [
                    "slug" => $slug,
                    "itemId" => $itemId,
                    "variationId" => $variationId
                ]
            );
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }

        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        $webstoreConfiguration = $webstoreConfigurationRepository->getWebstoreConfiguration();

        $attributeSelectDefaultOption = (int)$webstoreConfiguration->attributeSelectDefaultOption;
        $itemResult['initPleaseSelectOption'] = $variationId <= 0 && $attributeSelectDefaultOption === 1;
        return $this->renderTemplate(
            'tpl.item',
            $itemResult
        );
    }

    /**
     * @param int $itemId
     * @param int $variationId
     * @return string
     */
    public function showItemWithoutName(int $itemId, $variationId = 0)
    {
        return $this->showItem("", $itemId, $variationId);
    }

    /**
     * @param int $itemId
     * @return string
     */
    public function showItemFromAdmin(int $itemId)
    {
        return $this->showItem("", $itemId, 0);
    }

    public function showItemOld($name = null, $itemId = null)
    {
        if (is_null($itemId)) {
            $itemId = $name;
        }
        return $this->showItem("", (int)$itemId, 0);
    }

    public function showItemForCategory($category)
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);
        if(($previewVariationId = $request->get('previewVariationId', 0)) > 0) {
            // load given variation for the preview in the shop builder
            return $this->showItem('', 0, $previewVariationId, $category);
        }

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        // search for any item in the given category
        $itemList = $itemSearchService->getResult(
            CategoryItems::getSearchFactory(
                [
                    'categoryId' => $category->id,
                    'itemsPerPage' => 1
                ]
            )
        );

        if (is_array($itemList['documents']) && count($itemList['documents'])) {
            return $this->showItem(
                '',
                $itemList['documents'][0]['data']['item']['id'],
                $itemList['documents'][0]['data']['variation']['id'],
                $category
            );
        }

        // if no items were found in the given category load any item.
        return $this->showItem(
            '',
            0,
            0,
            $category
        );
    }
}
