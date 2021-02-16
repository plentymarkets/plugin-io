<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;

/**
 * Class OrderReturnResource
 *
 * Resource class for the route `io/order/return`.
 * @package IO\Api\Resources
 */
class OrderReturnResource extends ApiResource
{
    /**
     * @var OrderService $orderService The instance of the OrderService.
     */
    private $orderService;


    /**
     * OrderReturnResource constructor.
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
     * Create a return order for a specific order.
     * @return Response
     */
    public function store():Response
    {
        $returnOrder = $this->orderService->createOrderReturn(
            $this->request->get('orderId', 0),
            $this->request->get('orderAccessKey', ''),
            $this->request->get('variationIds', []),
            $this->request->get('returnNote', '')
        );

        return $this->response->create($returnOrder, ResponseCode::OK);
    }
}
