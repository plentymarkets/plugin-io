<?php

namespace IO\Api\Resources;

use IO\Services\ItemListService;
use IO\Services\ItemWishListService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class ItemWishListResource
 *
 * Resource class for the route `io/itemWishList`.
 * @package IO\Api\Resources
 */
class ItemWishListResource extends ApiResource
{
    /**
     * @var ItemWishListService $itemWishListService Instance of the ItemWishListService.
     */
    private $itemWishListService;

    /**
     * ItemWishListResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ItemWishListService $itemWishListService
     */
    public function __construct(Request $request, ApiResponse $response, ItemWishListService $itemWishListService)
    {
        parent::__construct($request, $response);

        $this->itemWishListService = $itemWishListService;
    }

    /**
     * Return the wishlist items of the current customer.
     * @return Response
     */
    public function index():Response
    {
        /** @var ItemListService $itemListService */
        $itemListService = pluginApp(ItemListService::class);
        $items = $itemListService->getItemList(ItemListService::TYPE_WISH_LIST);

        return $this->response->create($items, ResponseCode::OK);
    }

    /**
     * Add an item to the wishlist.
     * @return Response
     */
    public function store():Response
    {
        $variationId = $this->request->get('variationId', 0);
        $quantity = $this->request->get('quantity', 0);

        $itemWishList = $this->itemWishListService->addItemWishListEntry((INT)$variationId, (INT)$quantity);

        return $this->response->create($itemWishList, ResponseCode::CREATED);
    }

    /**
     * Remove an item from the wishlist.
     * @param string $selector
     */
    public function destroy(string $selector):Response
    {
        $itemWishList = $this->itemWishListService->removeItemWishListEntry((INT)$selector);

        return $this->response->create($itemWishList, ResponseCode::OK);
    }
}
