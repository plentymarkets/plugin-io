<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\BasketService;

/**
 * Class BasketResource
 * @package LayoutCore\Api\Resources
 */
class BasketResource extends ApiResource
{
	/**
	 * @var BasketService
	 */
	private $basketService;

    /**
     * BasketResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param BasketService $basketService
     */
	public function __construct(Request $request, ApiResponse $response, BasketService $basketService)
	{
		parent::__construct($request, $response);
		$this->basketService = $basketService;
	}

    /**
     * Get the basket
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		$basket = $this->basketService->getBasket();
		return $this->response->create($basket, ResponseCode::OK);
	}
}
