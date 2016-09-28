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
 * Class ItemVariationSelectResource
 * @package LayoutCore\Api\Resources
 */
class ItemVariationSelectResource extends ApiResource
{
	/**
	 * @var ItemService
	 */
	private $itemService;
    
    /**
     * ItemVariationSelectResource constructor.
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
     * get variation attributes by variation id
     * @param string $itemId
     * @return BaseResponse
     */
	public function show(string $itemId):BaseResponse
	{
		$attributeList = $this->itemService->getItemVariationAttributes((int)$itemId);
		return $this->response->create($attributeList, ResponseCode::OK);
	}
}
