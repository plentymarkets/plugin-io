<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Services\ItemSearch\SearchPresets\CrossSellingItems;
use IO\Services\ItemSearch\SearchPresets\SingleItem;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;

/**
 * Class ItemController
 * @package IO\Controllers
 */
class ItemController extends LayoutController
{
    /**
     * Prepare and render the item data.
     * @param string $slug
     * @param int $itemId The itemId read from current request url. Will be null if item url does not contain a slug.
     * @param int $variationId
     * @return string
     */
    public function showItem(
        string $slug = "",
        int $itemId = 0,
        int $variationId = 0
    )
    {
        $itemSearchOptions = [
            'itemId'        => $itemId,
            'variationId'   => $variationId,
            'setCategory'   => true
        ];
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemResult = $itemSearchService->getResult(
            SingleItem::getSearchFactory( $itemSearchOptions )
        );


        if(empty($itemResult['documents']))
        {
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
}
