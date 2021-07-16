<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;

/**
 * Class ShippingCountryResource
 *
 * Resource class for the route `io/shipping/country`.
 * @package IO\Api\Resources
 */
class ShippingCountryResource extends ApiResource
{
	/**
	 * @var CheckoutService $checkoutService The instance of the CheckoutService.
	 */
	private $checkoutService;

    /**
     * ShippingCountryResource constructor.
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
     * Save the given shipping country ID to the current session.
     * @return Response
     */
	public function store():Response
	{
		$shippingCountryId = (int)$this->request->get('shippingCountryId', 0);
		$this->checkoutService->setShippingCountryId($shippingCountryId);

		return $this->response->create($shippingCountryId, ResponseCode::OK);
	}
}
