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
 *
 * Resource class for the route `io/localization/language`.
 * @package IO\Api\Resources
 */
class LanguageResource extends ApiResource
{
    /**
    * @var LocalizationService $localizationService Instance of the LocalizationService.
    */
    private $localizationService;

    /**
     * LanguageResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param LocalizationService $localizationService
     */
	public function __construct(Request $request, ApiResponse $response, LocalizationService $localizationService)
	{
		parent::__construct($request, $response);
        $this->localizationService = $localizationService;
	}

    /**
     * Update the language.
     * @param string $newLanguage
     * @return Response
     */
	public function update(string $newLanguage):Response
	{
		$this->localizationService->setLanguage($newLanguage);
		return $this->response->create($newLanguage, ResponseCode::OK);
	}
}
