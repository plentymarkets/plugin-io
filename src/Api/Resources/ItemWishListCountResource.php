<?php

namespace IO\Api\Resources;

use IO\Services\ItemWishListService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class ItemWishListCountResource
 * @package IO\Api\Resources
 */
class ItemWishListCountResource extends ApiResource
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
}
