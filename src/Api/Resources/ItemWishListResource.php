<?php
/**
 * Created by IntelliJ IDEA.
 * User: ihussein
 * Date: 01.08.17
 * Time: 14:58
 */

namespace IO\Api\Resources;

use IO\Services\ItemWishListService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class BasketItemResource
 * @package IO\Api\Resources
 */
class ItemWishListResource extends ApiResource
{
    /**
     * @var ItemWishListService
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
     * List itemWishList for contact
     * @return Response
     */
    public function index():Response
    {
        $itemWishList = $this->itemWishListService->getItemWishList();
        return $this->response->create($itemWishList, ResponseCode::OK);
    }

    // Post
    /**
     * Add an item to the basket
     * @return Response
     */
    public function store():Response
    {
        $variationId = $this->request->get('variationId', 0);
        $quantity = $this->request->get('quantity', 0);

        $itemWishList = $this->itemWishListService->addItemWishListEntry((INT)$variationId, (INT)$quantity);

        return $this->response->create($itemWishList, ResponseCode::CREATED);
    }

    // Delete
    /**
     * Delete an item from the basket
     * @param string $selector
     * @return Response
     */
    public function destroy(string $selector):Response
    {
        $itemWishList = $this->itemWishListService->removeItemWishListEntry((INT)$selector);

        return $this->response->create($itemWishList, ResponseCode::OK);
    }
}
