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
 *
 * Resource class for the route `io/basket/items`.
 * @package IO\Api\Resources
 */
class BasketItemResource extends SessionResource
{
    /**
     * @var BasketService $basketService Instance of the BasketService.
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
     * Get basket items.
     * @return Response
     */
    public function index(): Response
    {

        return $this->response->create($this->indexBasketItems(), ResponseCode::OK);
    }

    /**
     * Add an item to the basket
     * @return Response
     */

    /**
     * Add an item in the basket.
     * @return Response
     */
    public function store(): Response
    {
        $this->basketService->setTemplate($this->request->get('template', ''));
        $result = $this->basketService->addBasketItem($this->request->all());

        if (array_key_exists("code", $result)) {
            return $this->response->create(
                ["exceptionCode" => $result["code"], 'placeholder' => $result['placeholder']],
                ResponseCode::BAD_REQUEST
            );
        }

        return $this->response->create(true, ResponseCode::CREATED);
    }

    /**
     * Get a basket item by ID.
     * @param string $selector ID of the basket item.
     * @return Response
     */
    public function show(string $selector): Response
    {
        $this->basketService->setTemplate($this->request->get('template', ''));
        $basketItem = $this->basketService->getBasketItem((int)$selector);
        return $this->response->create($basketItem, ResponseCode::OK);
    }

    // Put/patch

    /**
     * Update the basket item.
     * @param string $selector
     * @return Response
     */

    /**
     * Update a basket item by ID.
     * @param string $selector ID of the basket item.
     * @return Response
     */
    public function update(string $selector): Response
    {
        $this->basketService->setTemplate($this->request->get('template', ''));
        $result = $this->basketService->updateBasketItem((int)$selector, $this->request->all());

        if (array_key_exists("code", $result)) {
            return $this->response->create(
                ["exceptionCode" => $result["code"], 'placeholder' => $result['placeholder']],
                ResponseCode::BAD_REQUEST
            );
        }

        return $this->response->create(true, ResponseCode::OK);
    }

    // Delete

    /**
     * Delete an item from the basket.
     * @param string $selector
     * @return Response
     */

    /**
     * Delete an item from the basket by ID.
     * @param string $selector ID of the basket item.
     * @return Response
     */
    public function destroy(string $selector): Response
    {
        $this->basketService->setTemplate($this->request->get('template', ''));
        $this->basketService->deleteBasketItem((int)$selector);
        return $this->response->create(true, ResponseCode::OK);
    }
}
