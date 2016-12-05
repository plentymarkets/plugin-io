<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\ShippingService;

/**
 * Class ShippingResource
 * @package LayoutCore\Api\Resources
 */
class ShippingResource extends ApiResource
{

	/**
	 * @var ShippingService
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

	// Put/patch
    /**
     * Set the shipping profile
     * @param string $shippingProfileId
     * @return BaseResponse
     */
	public function update(string $shippingProfileId):BaseResponse
	{
		$this->shippingService->setShippingProfileId((int)$shippingProfileId);
		return $this->response->create(ResponseCode::OK);
	}

}
