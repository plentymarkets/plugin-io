<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;

/**
 * Class CustomerOrderReturnResource
 *
 * Resource class for the route `io/customer/order/return`.
 * @package IO\Api\Resources
 */
class CustomerOrderReturnResource extends ApiResource
{
    /**
     * @var CustomerService $customerService Instance of the CustomerService.
     */
    private $customerService;

    /**
     * CustomerOrderReturnResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CustomerService $customerService
     */
    public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
    {
        parent::__construct($request, $response);
        $this->customerService = $customerService;
    }

    /**
     * Get a list of return orders for the current contact.
     * @return Response
     */
    public function index():Response
    {
        $page = $this->request->get('page', 1);
        $items = $this->request->get('items', 10);
        $response = $this->customerService->getReturns($page, $items);

        return $this->response->create($response, ResponseCode::OK);
    }
}
