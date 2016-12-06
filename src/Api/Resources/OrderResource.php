<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;
use IO\Services\CustomerService;

/**
 * Class OrderResource
 * @package IO\Api\Resources
 */
class OrderResource extends ApiResource
{
    /**
     * OrderResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
	public function __construct(
		Request $request,
		ApiResponse $response)
	{
		parent::__construct($request, $response);
	}

    /**
     * List the orders of the customer
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		$page  = (int)$this->request->get("page", 1);
		$items = (int)$this->request->get("items", 10);

		$data = pluginApp(CustomerService::class)->getOrders($page, $items);
		return $this->response->create($data, ResponseCode::OK);
	}

    /**
     * Create an order
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$order = pluginApp(OrderService::class)->placeOrder();
		return $this->response->create($order, ResponseCode::OK);
	}
}
