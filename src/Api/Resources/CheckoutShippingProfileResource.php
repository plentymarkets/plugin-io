<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;
use Plenty\Plugin\Http\Response;

/**
 * Class CheckoutShippingProfilesResource
 *
 * Resource class for the route `io/checkout/shipping-profiles`.
 * @package IO\Api\Resources
 */
class CheckoutShippingProfilesResource extends ApiResource
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

    public function index(): Response
    {
        $shippingProfileList = $this->checkoutService->getShippingProfileList();
        $shippingProfileId = $this->checkoutService->getShippingProfileId();

        foreach ($shippingProfileList as $key => $shippingProfile) {
            if ($shippingProfile['parcelServicePresetId'] === $shippingProfileId) {
                $shippingProfile['selected'] = true;
            }
        }

        return $this->response->create($shippingProfileList, ResponseCode::OK);
    }
}
