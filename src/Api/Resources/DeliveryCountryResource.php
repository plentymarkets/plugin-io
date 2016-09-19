<?php //strict

namespace LayoutCore\Api\Resources;

use Illuminate\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\BasketService;
use LayoutCore\Services\CountryService;

class DeliveryCountryResource extends ApiResource
{
	/**
	 * @var BasketService
	 */
	private $basketService;
	/**
	 * @var CountryService
	 */
	private $countryService;
	
	public function __construct(Request $request, ApiResponse $response, BasketService $basketService, CountryService $countryService)
	{
		parent::__construct($request, $response);
		$this->basketService  = $basketService;
		$this->countryService = $countryService;
	}
	
	
	// put/patch
	public function update(string $shippingCountryId):Response
	{
		$this->countryService->setShippingCountryId((int)$shippingCountryId);
		$basket = $this->basketService->getBasket();
		return $this->response->create($basket, ResponseCode::OK);
	}
	
	
}
