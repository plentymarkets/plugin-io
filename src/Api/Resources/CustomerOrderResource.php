<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;

/**
 * Class CustomerOrderResource
 *
 * Resource class for the route `io/customer/order/list`.
 * @package IO\Api\Resources
 */
class CustomerOrderResource extends ApiResource
{
    /**
     * @var OrderService $orderService Instance of the OrderService.
     */
    private $orderService;

    /**
     * CustomerOrderResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param OrderService $orderService
     */
    public function __construct(Request $request, ApiResponse $response, OrderService $orderService)
    {
        parent::__construct($request, $response);
        $this->orderService = $orderService;
    }

    /**
     * Get a list of orders for a contact in a compact and reduced format.
     * @return Response
     */
    public function index():Response
    {
        $page = $this->request->get('page', 1);
        $items = $this->request->get('items', 10);
        $response = $this->orderService->getOrdersCompact($page, $items);

        return $this->response->create($response, ResponseCode::OK);
    }
}
