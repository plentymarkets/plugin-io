<?php //strict

namespace IO\Api;

use IO\Middlewares\DetectCurrency;
use IO\Middlewares\DetectLanguage;
use IO\Middlewares\DetectReferrer;
use IO\Middlewares\DetectShippingCountry;
use IO\Services\TemplateService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;

/**
 * Class ApiResource
 * @package IO\Api
 */
class ApiResource extends Controller
{
    /**
     * @var \IO\Api\ApiResponse
     */
	protected $response;

    /**
     * @var Request
     */
	protected $request;

	/**
	 * @var ResponseCode
	 */
	private $defaultCode = ResponseCode::NOT_IMPLEMENTED;

    /**
     * ApiResource constructor.
     * @param Request $request
     * @param \IO\Api\ApiResponse $response
     */
	public function __construct(Request $request, ApiResponse $response)
	{
        $this->response = $response;
		$this->request  = $request;
        $initialRestCall = $request->get('initialRestCall', false);

        if ($initialRestCall) {
            $this->detectData();
        }

        $templateEvent = $request->get('templateEvent', '');
        if(empty(TemplateService::$currentTemplate) && strlen($templateEvent))
        {
            TemplateService::$currentTemplate = $templateEvent;
        }
    }

    private function detectData()
    {
        /** @var DetectReferrer $detectReferrer */
        $detectReferrer = pluginApp(DetectReferrer::class);
        $detectReferrer->before($this->request);

        /** @var DetectShippingCountry $detectShippingCountry */
        $detectShippingCountry = pluginApp(DetectShippingCountry::class);
        $detectShippingCountry->before($this->request);

        /** @var DetectCurrency $detectCurrency */
        $detectCurrency = pluginApp(DetectCurrency::class);
        $detectCurrency->before($this->request);

        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        /** @var DetectLanguage $detectLanguage */
        $detectLanguage = pluginApp(DetectLanguage::class);
        $detectLanguage->detectLanguage($this->request, $sessionStorageRepository->getHttpReferrerUri());
    }

    /**
     * Triggered on REST calls with the type GET.
     * @return Response
     */
	public function index():Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

    /**
     * Triggered on REST calls with the type POST.
     * @return Response
     */
	public function store():Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

    /**
     * Triggered on REST calls with the type GET with path parameters.
     * @param string $selector
     * @return Response
     */
	public function show(string $selector):Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

    /**
     * Triggered on REST calls with the type PUT.
     * @param string $selector
     * @return Response
     */
	public function update(string $selector):Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

    /**
     * Triggered on REST calls with the type DELETE.
     * @param string $selector
     * @return Response
     */
	public function destroy(string $selector):Response
	{
		return $this->response->create(null, $this->defaultCode);
	}
}
