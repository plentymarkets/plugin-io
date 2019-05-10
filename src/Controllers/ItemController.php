<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Services\CategoryService;
use IO\Services\ItemListService;
use IO\Services\ItemSearch\Factories\VariationSearchResultFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\CrossSellingItems;
use IO\Services\ItemSearch\SearchPresets\SingleItem;
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

    /**
     * Prepare and render the item data.
     * @param string    $slug
     * @param int       $itemId         The itemId read from current request url. Will be null if item url does not contain a slug.
     * @param int       $variationId
     * @param Category  $category
     * @return string
     * @throws \ErrorException
     */
    public function showItem(
        string $slug = "",
        int $itemId = 0,
        int $variationId = 0,
        $category = null
    )
    {
        $itemSearchOptions = [
            'itemId'        => $itemId,
            'variationId'   => $variationId,
            'setCategory'   => is_null($category)
        ];
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemResult = $itemSearchService->getResult(
            SingleItem::getSearchFactory( $itemSearchOptions )
        );

        if (!is_null($category))
        {
            pluginApp(CategoryService::class)->setCurrentCategory($category);
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        if ($shopBuilderRequest->isShopBuilder())
        {
            /** @var VariationSearchResultFactory $searchResultFactory */
            $searchResultFactory = pluginApp(VariationSearchResultFactory::class);
            $itemResult = $searchResultFactory->fillSearchResults(
                $itemResult,
                ResultFieldTemplate::get(ResultFieldTemplate::TEMPLATE_SINGLE_ITEM)
            );
        }

        if(empty($itemResult['documents']))
        {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.ItemController_itemNotFound",
                [
                    "slug"          => $slug,
                    "itemId"        => $itemId,
                    "variationId"   => $variationId
                ]
            );
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }
        else
        {
            return $this->renderTemplate(
                'tpl.item',
                [
                    'item' => $itemResult
                ]
            );
        }
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
        if(is_null($itemId))
        {
            $itemId = $name;
        }

        return $this->showItem("", (int)$itemId, 0);
    }

    public function showItemForCategory($category = null)
    {

        /** @var ItemListService $itemListService */
        $itemListService = pluginApp(ItemListService::class);
        $itemList = $itemListService->getItemList(ItemListService::TYPE_CATEGORY, $category->id, null, 1);
        if (!count($itemList['documents']))
        {
            $itemList = $itemListService->getItemList(ItemListService::TYPE_RANDOM);
        }

        return $this->showItem(
            '',
            $itemList['documents'][0]['data']['ids']['itemId'],
            $itemList['documents'][0]['data']['variation']['id'],
            $category
        );
    }
}
