<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;

class CustomerOrderReturnResource extends ApiResource
{
    private $customerService;
    
    public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
    {
        parent::__construct($request, $response);
        $this->customerService = $customerService;
    }
    
    public function index():Response
    {
        $page = $this->request->get('page', 1);
        $items = $this->request->get('items', 10);
        $response = $this->customerService->getReturns($page, $items);
        
        return $this->response->create($response, ResponseCode::OK);
    }
}