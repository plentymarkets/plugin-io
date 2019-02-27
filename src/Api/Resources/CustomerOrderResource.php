<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;

class CustomerOrderResource extends ApiResource
{
    private $orderService;
    
    public function __construct(Request $request, ApiResponse $response, OrderService $orderService)
    {
        parent::__construct($request, $response);
        $this->orderService = $orderService;
    }
    
    public function index():Response
    {
        $page = $this->request->get('page', 1);
        $items = $this->request->get('items', 10);
        $response = $this->orderService->getOrderOverviewListForMyAccount($page, $items);
        
        return $this->response->create($response, ResponseCode::OK);
    }
}