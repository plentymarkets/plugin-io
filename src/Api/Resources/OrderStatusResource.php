<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\OrderService;

/**
 * Class OrderStatusResource
 * @package LayoutCore\Api\Resources
 */
class OrderStatusResource extends ApiResource
{
    /**
     * @var OrderService
     */
    private $orderService;
    
    /**
     * OrderStatusResource constructor.
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
     * @param string $statusId
     * @return BaseResponse
     */
    public function show(string $statusId):BaseResponse
    {
        $statusText = '';
        
        if((int)$statusId > 0)
        {
            $statusText = $this->orderService->getOrderStatusText((int)$statusId);
        }
        
        return $this->response->create($statusText, ResponseCode::OK);
    }
}