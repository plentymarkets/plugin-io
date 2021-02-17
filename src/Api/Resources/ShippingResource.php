<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ShippingService;

/**
 * Class ShippingResource
 * @package IO\Api\Resources
 * @deprecated will be removed in 6.0.0.
 */
class ShippingResource extends ApiResource
{
	/**
	 * @var ShippingService $shippingService The instance of the current ShippingService.
	 */
	private $shippingService;

    /**
     * ShippingResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ShippingService $shippingService
     */
	public function __construct(Request $request, ApiResponse $response, ShippingService $shippingService)
	{
		parent::__construct($request, $response);
		$this->shippingService = $shippingService;
	}

    /**
     * Update the selected shipping profile.
     * @param string $shippingProfileId The ID of the shipping profile to switch to.
     * @return Response
     */
	public function update(string $shippingProfileId):Response
	{
		$this->shippingService->setShippingProfileId((int)$shippingProfileId);
		return $this->response->create(ResponseCode::OK);
	}

}
