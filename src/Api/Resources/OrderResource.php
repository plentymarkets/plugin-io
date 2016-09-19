<?php //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Request;
use Illuminate\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Helper\AbstractFactory;
use LayoutCore\Services\OrderService;
use LayoutCore\Services\CustomerService;

class OrderResource extends ApiResource
{
	/**
	 * @var AbstractFactory
	 */
	private $factory;
	
	public function __construct(
		Request $request,
		ApiResponse $response,
		AbstractFactory $factory)
	{
		parent::__construct($request, $response);
		$this->factory = $factory;
	}
	
	public function index():Response
	{
		$page  = (int)$this->request->get("page", 1);
		$items = (int)$this->request->get("items", 50);
		
		$data = $this->factory->make(CustomerService::class)->getOrders($page, $items);
		return $this->response->create($data, ResponseCode::OK);
	}
	
	public function store():Response
	{
		$order = $this->factory->make(OrderService::class)->placeOrder();
		return $this->response->create($order, ResponseCode::OK);
	}
}
