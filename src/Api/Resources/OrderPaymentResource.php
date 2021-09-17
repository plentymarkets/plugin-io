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
 *
 * Resource class for the route `io/order/payment`.
 * @package IO\Api\Resources
 */
class OrderPaymentResource extends ApiResource
{
    /**
     * @var OrderService $orderService The instance of the OrderService.
     */
    private $orderService;

    /**
     * OrderPaymentResource constructor.
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
     * Check if it is possible to switch to another payment method from a specific one.
     * @return Response
     */
    public function index():Response
    {
        $paymentMethodId = $this->request->get('paymentMethodId');
        $orderId = $this->request->get('orderId');
        $response = $this->orderService->allowPaymentMethodSwitchFrom($paymentMethodId, $orderId);

        return $this->response->create($response, ResponseCode::OK);
    }

    /**
     * Switch the payment method of an order to a new payment method.
     * @return Response
     */
    public function store():Response
    {
        $orderId = $this->request->get('orderId', 0);
        $paymentMethodId = $this->request->get('paymentMethodId', 0);
        $accessKey = $this->request->get('accessKey', '');

        if(strlen($accessKey)) {
            $response = $this->orderService->switchPaymentMethodForOrder($orderId, $paymentMethodId, $accessKey);
            return $this->response->create($response, ResponseCode::CREATED);
        }
        return $this->response->create(null, ResponseCode::UNAUTHORIZED);
    }

    /**
     * List all payment methods available for switch in MyAccount.
     * @return Response
     */
	public function paymentMethodListForSwitch():Response
	{
		$paymentMethodId = $this->request->get('paymentMethodId');
		$orderId = $this->request->get('orderId');
		$response = $this->orderService->getPaymentMethodListForSwitch($paymentMethodId, $orderId);
		return $this->response->create($response, ResponseCode::CREATED);
	}
}
