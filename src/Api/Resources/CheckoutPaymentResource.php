<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;
use Plenty\Plugin\Http\Response;

/**
 * Class CheckoutPaymentResource
 * @package IO\Api\Resources
 */
class CheckoutPaymentResource extends ApiResource
{
    /**
     * @var CheckoutService
     */
    private $checkoutService;

    /**
     * CheckoutPaymentResource constructor.
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
     * Prepare the payment
     * @return BaseResponse
     */
    public function store():BaseResponse
    {
        $event = $this->checkoutService->preparePayment();
        return $this->response->create( $event, ResponseCode::OK );
    }
}
