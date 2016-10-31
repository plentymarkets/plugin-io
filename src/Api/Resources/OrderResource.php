<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Helper\AbstractFactory;
use LayoutCore\Services\OrderService;
use LayoutCore\Services\CustomerService;

/**
 * Class OrderResource
 * @package LayoutCore\Api\Resources
 */
class OrderResource extends ApiResource
{
	/**
	 * @var AbstractFactory
	 */
	private $factory;

    /**
     * OrderResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param AbstractFactory $factory
     */
	public function __construct(
		Request $request,
		ApiResponse $response,
		AbstractFactory $factory)
	{
		parent::__construct($request, $response);
		$this->factory = $factory;
	}

    /**
     * List the orders of the customer
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		$page  = (int)$this->request->get("page", 1);
		$items = (int)$this->request->get("items", 10);

		$data = $this->factory->make(CustomerService::class)->getOrders($page, $items);
		return $this->response->create($data, ResponseCode::OK);
	}

    /**
     * Create an order
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$order = $this->factory->make(OrderService::class)->placeOrder();
		return $this->response->create($order, ResponseCode::OK);
	}
}
