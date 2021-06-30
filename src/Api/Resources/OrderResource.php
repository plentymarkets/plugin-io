<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;
use IO\Services\CustomerService;

/**
 * Class OrderResource
 *
 * Resource class for the route `io/order`.
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
     * List the orders of the customer.
     * @return Response
     */
	public function index():Response
	{
	    /** @var CustomerService $customerService */
	    $customerService = pluginApp(CustomerService::class);
		$page  = (int)$this->request->get("page", 1);
		$items = (int)$this->request->get("items", 10);

		$data = $customerService->getOrders($page, $items);
		return $this->response->create($data, ResponseCode::OK);
	}

    /**
     * Place an order.
     * @return Response
     */
	public function store():Response
	{
	    /** @var OrderService $orderService */
	    $orderService = pluginApp(OrderService::class);
		$order = $orderService->placeOrder();
		return $this->response->create($order, ResponseCode::OK);
	}
}
