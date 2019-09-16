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
        $returnOrder = $this->orderService->createOrderReturn(
            $this->request->get('orderId', 0),
            $this->request->get('orderAccessKey', ''),
            $this->request->get('variationIds', []),
            $this->request->get('returnNote', '')
        );
        
        return $this->response->create($returnOrder, ResponseCode::OK);
    }
}