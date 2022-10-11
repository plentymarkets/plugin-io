<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CountryService;

/**
 * Class CountriesResource
 *
 * Resource class for the route `io/localization/countries`.
 * @package IO\Api\Resources
 */
class CountriesResource extends ApiResource
{
    /**
    * @var CountryService $countryService Instance of the CountryService.
    */
    private $countryService;

    /**
     * LanguageResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CountryService $countryService
     */
	public function __construct(Request $request, ApiResponse $response, CountryService $countryService)
	{
		parent::__construct($request, $response);
        $this->$countryService = $countryService;
	}

    /**
     * Execute a search for items for a given query.
     * @return Response
     * @throws \Exception
     */
    public function index(): Response
    {
        $response = $this->countryService->getAllCountries();
        return $this->response->create($response, ResponseCode::OK);
    }
}
