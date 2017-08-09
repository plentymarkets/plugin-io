<?php //strict

namespace IO\Api\Resources;

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
     * @return Response
     */
	public function index():Response
	{
		$basketItems = $this->basketService->getBasketItemsForTemplate($this->request->get('template', ''));
		return $this->response->create($basketItems, ResponseCode::OK);
	}

	// Post
    /**
     * Add an item to the basket
     * @return Response
     */
	public function store():Response
	{
        $this->basketService->setTemplate($this->request->get('template', ''));
		$basketItems = $this->basketService->addBasketItem($this->request->all());

        if(array_key_exists("code", $basketItems))
        {
            return $this->response->create(["exceptionCode" => $basketItems["code"]], ResponseCode::BAD_REQUEST);
        }

		return $this->response->create($basketItems, ResponseCode::CREATED);
	}

	// Get
    /**
     * Get a basket item
     * @param string $selector
     * @return Response
     */
	public function show(string $selector):Response
	{
        $this->basketService->setTemplate($this->request->get('template', ''));
		$basketItem = $this->basketService->getBasketItem((int)$selector);
		return $this->response->create($basketItem, ResponseCode::OK);
	}

	// Put/patch
    /**
     * Update the basket item
     * @param string $selector
     * @return Response
     */
	public function update(string $selector):Response
	{
        $this->basketService->setTemplate($this->request->get('template', ''));
		$basketItems = $this->basketService->updateBasketItem((int)$selector, $this->request->all());
		return $this->response->create($basketItems, ResponseCode::OK);
	}

	// Delete
    /**
     * Delete an item from the basket
     * @param string $selector
     * @return Response
     */
	public function destroy(string $selector):Response
	{
        $this->basketService->setTemplate($this->request->get('template', ''));
		$basketItems = $this->basketService->deleteBasketItem((int)$selector);
		return $this->response->create($basketItems, ResponseCode::OK);
	}
}
