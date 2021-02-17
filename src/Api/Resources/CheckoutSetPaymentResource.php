<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;
use Plenty\Plugin\Http\Response;

/**
 * Class CheckoutSetPaymentResource
 *
 * Resource class for the route `io/checkout/paymentId`.
 * @package IO\Api\Resources
 */
class CheckoutSetPaymentResource extends ApiResource
{
    /**
     * @var CheckoutService $checkoutService Instance of the CheckoutService.
     */
    private $checkoutService;

    /**
     * CheckoutSetPaymentResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CheckoutService $checkoutService
     */
    public function __construct( Request $request, ApiResponse $response, CheckoutService $checkoutService )
    {
        parent::__construct( $request, $response );
        $this->checkoutService = $checkoutService;
    }

    /**
     * Set the method of payment ID.
     * @return Response
     */
    public function store():Response
    {
        $paymentId = $this->request->get('paymentId', 0);

        $this->checkoutService->setMethodOfPaymentId($paymentId);

        return $this->response->create( $paymentId, ResponseCode::OK );
    }
}
