<?php //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\ItemService;

class ItemVariationSelectResource extends ApiResource
{
	/**
	 * @var ItemService
	 */
	private $itemService;
	
	public function __construct(Request $request, ApiResponse $response, ItemService $itemService)
	{
		parent::__construct($request, $response);
		$this->itemService = $itemService;
	}
	
	public function index():Response
	{
		return $this->response->create(null, ResponseCode::OK);
	}
	
	public function show(string $itemId):Response
	{
		$attributeList = $this->itemService->getItemVariationAttributes((int)$itemId);
		return $this->response->create($attributeList, ResponseCode::OK);
	}
}
