<?php //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\BasketService;

class BasketItemResource extends ApiResource
{
	/**
	 * @var BasketService
	 */
	private $basketService;
	
	public function __construct(Request $request, ApiResponse $response, BasketService $basketService)
	{
		parent::__construct($request, $response);
		$this->basketService = $basketService;
	}
	
	public function index():Response
	{
		$basketItems = $this->basketService->getBasketItems();
		return $this->response->create($basketItems, ResponseCode::OK);
	}
	
	// post
	public function store():Response
	{
		$basketItems = $this->basketService->addBasketItem($this->request->all());
		return $this->response->create($basketItems, ResponseCode::CREATED);
	}
	
	// get
	public function show(string $selector):Response
	{
		$basketItem = $this->basketService->getBasketItem((int)$selector);
		return $this->response->create($basketItem, ResponseCode::OK);
	}
	
	// put/patch
	public function update(string $selector):Response
	{
		$basketItems = $this->basketService->updateBasketItem((int)$selector, $this->request->all());
		return $this->response->create($basketItems, ResponseCode::OK);
	}
	
	// delete
	public function destroy(string $selector):Response
	{
		$basketItems = $this->basketService->deleteBasketItem((int)$selector);
		return $this->response->create($basketItems, ResponseCode::OK);
	}
}
