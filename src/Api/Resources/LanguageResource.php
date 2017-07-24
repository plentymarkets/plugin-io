<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\LocalizationService;

/**
 * Class LanguageResource
 * @package IO\Api\Resources
 */
class LanguageResource extends ApiResource
{
    /**
    * @var
    */
    private $localizationService;

    /**
     * LanguageResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
	public function __construct(Request $request, ApiResponse $response, LocalizationService $localizationService)
	{
		parent::__construct($request, $response);
        $this->localizationService = $localizationService;
	}

    /**
     * Updates the shop language. (put)
     * @param string $newLanguage
     * @return Response
     */
	public function update(string $newLanguage):Response
	{
		$this->localizationService->setLanguage($newLanguage);
		return $this->response->create($newLanguage, ResponseCode::OK);
	}
}
