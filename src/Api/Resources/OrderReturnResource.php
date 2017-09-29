<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;

class OrderReturnResource extends ApiResource
{
    private $orderService;
    
    public function __construct(Request $request, ApiResponse $response, OrderService $orderService)
    {
        parent::__construct($request, $response);
        $this->orderService = $orderService;
    }
    
    public function store():Response
    {
        $orderId = $this->request->get('orderId', 0);
        $variationIds = $this->request->get('variationIds', []);
        
        $this->orderService->createOrderReturn($orderId, $variationIds);
        
        return $this->response->create([], ResponseCode::OK);
    }
}