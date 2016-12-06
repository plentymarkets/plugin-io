<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\BasketService;

/**
 * Class BasketItemResource
 * @package IO\Api\Resources
 */
class BasketItemResource extends ApiResource
{
	/**
	 * @var BasketService
	 */
	private $basketService;

    /**
     * BasketItemResource constructor.
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
     * List basket items
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		$basketItems = $this->basketService->getBasketItems();
		return $this->response->create($basketItems, ResponseCode::OK);
	}

	// Post
    /**
     * Add an item to the basket
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$basketItems = $this->basketService->addBasketItem($this->request->all());
		return $this->response->create($basketItems, ResponseCode::CREATED);
	}

	// Get
    /**
     * Get a basket item
     * @param string $selector
     * @return BaseResponse
     */
	public function show(string $selector):BaseResponse
	{
		$basketItem = $this->basketService->getBasketItem((int)$selector);
		return $this->response->create($basketItem, ResponseCode::OK);
	}

	// Put/patch
    /**
     * Update the basket item
     * @param string $selector
     * @return BaseResponse
     */
	public function update(string $selector):BaseResponse
	{
		$basketItems = $this->basketService->updateBasketItem((int)$selector, $this->request->all());
		return $this->response->create($basketItems, ResponseCode::OK);
	}

	// Delete
    /**
     * Delete an item from the basket
     * @param string $selector
     * @return BaseResponse
     */
	public function destroy(string $selector):BaseResponse
	{
		$basketItems = $this->basketService->deleteBasketItem((int)$selector);
		return $this->response->create($basketItems, ResponseCode::OK);
	}
}
