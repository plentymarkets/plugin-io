<?php //strict

namespace IO\Api\Resources;

use IO\Middlewares\DetectCurrency;
use IO\Middlewares\DetectReferrer;
use IO\Middlewares\DetectShippingCountry;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\BasketService;

/**
 * Class BasketResource
 * @package IO\Api\Resources
 */
class BasketResource extends ApiResource
{
    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var VatService
     */
    private $vatService;

    /**
     * BasketResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param BasketService $basketService
     * @param VatService $vatService
     */
    public function __construct(Request $request, ApiResponse $response, BasketService $basketService, VatService $vatService)
    {
        parent::__construct($request, $response);
        $this->basketService = $basketService;
        $this->vatService    = $vatService;
    }

    /**
     * Get the basket
     * @return Response
     */
    public function index(): Response
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
        
        $basket                  = $this->basketService->getBasketForTemplate();
        $basket['totalVats']     = $this->vatService->getCurrentTotalVats();

		return $this->response->create($basket, ResponseCode::OK);
	}
}
