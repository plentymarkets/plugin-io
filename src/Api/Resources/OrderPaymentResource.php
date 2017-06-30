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
        $this->orderService = $orderService;
    }
    
    public function index():Response
    {
        $paymentMethodId = $this->request->get('paymentMethodId');
        $orderId = $this->request->get('orderId');
        $response = $this->orderService->allowPaymentMethodSwitchFrom($paymentMethodId, $orderId);
    
        return $this->response->create($response, ResponseCode::OK);
    }
    
    public function store():Response
    {
        $orderId = $this->request->get('orderId', 0);
        $paymentMethodId = $this->request->get('paymentMethodId', 0);
        
        $response = $this->orderService->switchPaymentMethodForOrder($orderId, $paymentMethodId);
        
        return $this->response->create($response, ResponseCode::CREATED);
    }
	
	public function paymentMethodListForSwitch():Response
	{
		$paymentMethodId = $this->request->get('paymentMethodId');
		$orderId = $this->request->get('orderId');
		$response = $this->orderService->getPaymentMethodListForSwitch($paymentMethodId, $orderId);
		return $this->response->create($response, ResponseCode::CREATED);
	}
}
