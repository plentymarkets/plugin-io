<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;

/**
 * Class ShippingCountryIdResource
 * @package IO\Api\Resources
 */
class ShippingCountryResource extends ApiResource
{
	/**
	 * @var CheckoutService
	 */
	private $checkoutService;

    /**
     * CheckoutResource constructor.
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
     * Save shippingCountryId to the session
     * @return Response
     */
	public function store():Response
	{
		$shippingCountryId = (int)$this->request->get('shippingCountryId', 0);
		$this->checkoutService->setShippingCountryId($shippingCountryId);

		return $this->response->create($shippingCountryId, ResponseCode::OK);
	}
}
