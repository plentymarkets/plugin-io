<?php //strict

namespace IO\Api\Resources;

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
class CheckoutSetShippingIdResource extends ApiResource
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
     * @return Response
     */
    public function store():Response
    {
        $shippingId = $this->request->get('shippingId', 0);

        $this->checkoutService->setShippingProfileId($shippingId);

        return $this->response->create( $shippingId, ResponseCode::OK );
    }
}
