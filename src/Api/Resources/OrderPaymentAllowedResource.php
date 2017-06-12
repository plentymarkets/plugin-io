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
class OrderPaymentAllowedResource extends ApiResource
{
    /**
     * @var OrderService
     */
    private $orderService;
    
    /**
     * OrderPaymentResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response, OrderService $orderService)
    {
        parent::__construct($request, $response);
    }
    
    public function index():Response
    {
        $paymentMethodId = $this->request->get('paymentMethodId');
        $orderId = $this->request->get('orderId');
        $response = $this->orderService->allowPaymentMethodSwitchFrom($paymentMethodId, $orderId);
        
        return $this->response->create($response, ResponseCode::OK);
    }
}
