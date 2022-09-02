<?php


namespace IO\Api\Resources;


use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CountryService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class ActiveShippingCountriesResource
 * @package IO\Api\Resources
 */
class ActiveShippingCountriesResource extends ApiResource
{
    /** @var CountryService $countryService  */
    private $countryService;

    /**
     * ActiveShippingCountriesResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CountryService $countryService
     */
    public function __construct(Request $request, ApiResponse $response, CountryService $countryService)
    {
        parent::__construct($request, $response);
        $this->countryService = $countryService;
    }

    public function index(): Response
    {
        $lang = $this->request->get('lang', null);
        $countries = $this->countryService->getActiveCountriesList($lang);

        return $this->response->create($countries, ResponseCode::OK);
    }
}