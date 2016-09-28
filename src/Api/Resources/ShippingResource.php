<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseReponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\ShippingService;

class ShippingResource extends ApiResource
{
	
	/**
	 * @var ShippingService
	 */
	private $shippingService;
	
	public function __construct(Request $request, ApiResponse $response, ShippingService $shippingService)
	{
		parent::__construct($request, $response);
		$this->shippingService = $shippingService;
	}
	
	// put/patch
	public function update(string $shippingProfileId):BaseReponse
	{
		$this->shippingService->setShippingProfileId((int)$shippingProfileId);
		return $this->response->create(ResponseCode::OK);
	}
	
}
