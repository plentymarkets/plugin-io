<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\BasketService;

/**
 * Class BasketResource
 * @package IO\Api\Resources
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
     * @return Response
     */
	public function index():Response
	{
		$basket = $this->basketService->getBasket();
		return $this->response->create($basket, ResponseCode::OK);
	}
}
