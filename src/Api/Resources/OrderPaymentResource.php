<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;

/**
 * Class OrderPaymentResource
 * @package IO\Api\Resources
 */
class OrderPaymentResource extends ApiResource
{
    /**
     * OrderPaymentResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    public function store():Response
    {
        $orderId = $this->request->get('orderId', 0);
        $paymentMethodId = $this->request->get('paymentMethodId', 0);
        
        /**
         * @var OrderService $orderService
         */
        $orderService = pluginApp(OrderService::class);
        $response = $orderService->switchPaymentMethodForOrder($orderId, $paymentMethodId);
        
        return $this->response->create($response, ResponseCode::CREATED);
    }
}
