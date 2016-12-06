<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\BasketService;
use IO\Services\CountryService;

/**
 * Class DeliveryCountryResource
 * @package IO\Api\Resources
 */
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

    /**
     * DeliveryCountryResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param BasketService $basketService
     * @param CountryService $countryService
     */
	public function __construct(Request $request, ApiResponse $response, BasketService $basketService, CountryService $countryService)
	{
		parent::__construct($request, $response);
		$this->basketService  = $basketService;
		$this->countryService = $countryService;
	}


	// Put/patch
    /**
     * Set the shipping country
     * @param string $shippingCountryId
     * @return BaseResponse
     */
	public function update(string $shippingCountryId):BaseResponse
	{
		$this->countryService->setShippingCountryId((int)$shippingCountryId);
		$basket = $this->basketService->getBasket();
		return $this->response->create($basket, ResponseCode::OK);
	}


}
