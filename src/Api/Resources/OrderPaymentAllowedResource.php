<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;

/**
 * Class OrderPaymentAllowedResource
 * @package IO\Api\Resources
 * @deprecated will be removed in 6.0.0.
 */
class OrderPaymentAllowedResource extends ApiResource
{
    /**
     * @var OrderService $orderService The instance of the OrderService.
     */
    private $orderService;

    /**
     * OrderPaymentAllowedResource constructor.
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
     * @inheritDoc
     */
    public function index():Response
    {
        $paymentMethodId = $this->request->get('paymentMethodId', 0);
        $orderId = $this->request->get('orderId', 0);
        $response = $this->orderService->allowPaymentMethodSwitchFrom($paymentMethodId, $orderId);

        return $this->response->create($response, ResponseCode::OK);
    }
}
