<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\ItemService;

/**
 * Class CategoryItemsListResource
 * @package LayoutCore\Api\Resources
 */
class CategoryItemsListResource extends ApiResource
{
	/**
	 * @var ItemService
	 */
	private $itemService;

    /**
     * CategoryItemsListResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ItemService $itemService
     */
	public function __construct(Request $request, ApiResponse $response, ItemService $itemService)
	{
		parent::__construct($request, $response);
		$this->itemService = $itemService;
	}

    /**
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		return $this->response->create(null, ResponseCode::OK);
	}
    
    /**
     * List all items of a specific category
     * @param string $categoryId
     * @return BaseResponse
     */
	public function show(string $categoryId):BaseResponse
	{
		$itemsList = $this->itemService->getItemForCategory((int)$categoryId);
		return $this->response->create($itemsList, ResponseCode::OK);
	}
}
