<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;
use Plenty\Plugin\Http\Response;

/**
 * Class CheckoutSetShippingIdResource
 *
 * Resource class for the route `io/checkout/shippingId`.
 * @package IO\Api\Resources
 */
class CheckoutSetShippingIdResource extends ApiResource
{
    /**
     * @var CheckoutService $checkoutService Instance of the checkout.
     */
    private $checkoutService;

    /**
     * CheckoutSetShippingIdResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CheckoutService $checkoutService
     */
    public function __construct(Request $request, ApiResponse $response, CheckoutService $checkoutService)
    {
        parent::__construct($request, $response);
        $this->checkoutService = $checkoutService;
    }

    /**
     * Prepare the payment.
     * @return Response
     */

    /**
     * @return Response
     */

    /**
     * Set the shipping ID and the method of payment ID.
     * @return Response
     */
    public function store(): Response
    {
        $shippingId = $this->request->get('shippingId', 0);
        $methodOfPaymentId = $this->request->get('methodOfPaymentId', 0);

        if ($methodOfPaymentId > 0) {
            $this->checkoutService->setMethodOfPaymentId($methodOfPaymentId);
        }

        if ($this->checkoutService->getShippingProfileId() !== $shippingId) {
            $this->checkoutService->setShippingProfileId($shippingId);
        }

        return $this->response->create($shippingId, ResponseCode::OK);
    }
}
