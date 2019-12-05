<?php

namespace IO\Controllers;

use Ceres\Config\CeresConfig;
use IO\Api\ResponseCode;
use IO\Helper\Utils;
use IO\Services\CategoryService;
use IO\Services\ItemListService;
use IO\Services\ItemSearch\Factories\VariationSearchResultFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\SingleItem;
use IO\Services\ItemSearch\SearchPresets\VariationAttributeMap;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemController
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

        /** @var CeresConfig $ceresItemConfig */
        $ceresConfig = pluginApp(CeresConfig::class);
        if ($variationId > 0 && $ceresConfig->item->showPleaseSelect) {
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

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        $defaultCategories = $itemResult['item']['documents'][0]['data']['defaultCategories'];
        $defaultCategory = array_filter($defaultCategories, function ($category) {
            return $category['plentyId'] == $this->plentyId;
        });

        $shopBuilderRequest->setMainCategory($defaultCategory[0]['id']);
        $shopBuilderRequest->setMainContentType('singleitem');
        if ($shopBuilderRequest->isShopBuilder()) {
            /** @var VariationSearchResultFactory $searchResultFactory */
            $searchResultFactory = pluginApp(VariationSearchResultFactory::class);
            $itemResult['item'] = $searchResultFactory->fillSearchResults(
                $itemResult['item'],
                ResultFieldTemplate::get(ResultFieldTemplate::TEMPLATE_SINGLE_ITEM)
            );
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
        /** @var ItemListService $itemListService */
        $itemListService = pluginApp(ItemListService::class);
        $itemList = $itemListService->getItemList(ItemListService::TYPE_CATEGORY, $category->id, null, 1);
        if (count($itemList['documents'])) {
            return $this->showItem(
                '',
                $itemList['documents'][0]['data']['item']['id'],
                $itemList['documents'][0]['data']['variation']['id'],
                $category
            );
        }

        return $this->showItem(
            '',
            0,
            0,
            $category
        );
    }
}
