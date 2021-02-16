<?php

namespace IO\Middlewares;

use IO\Services\CheckoutService;
use IO\Services\CountryService;
use IO\Services\TemplateService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class DetectShippingCountry
 *
 * Set shipping country, if necessary.
 *
 * @package IO\Middlewares
 */
class DetectShippingCountry extends Middleware
{
    /**
     * Before the request is processed, the shipping country is changed, if necessary.
     *
     * Example request: ?ShipToCountry=COUNTRYID
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        $shipToCountry = $request->get('ShipToCountry', null);
        if ($shipToCountry != null) {
            /** @var CountryService $countryService */
            $countryService = pluginApp(CountryService::class);
            $country = $countryService->getCountryById($shipToCountry);
            if (!is_null($country) && $country->active) {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setShippingCountryId($shipToCountry);
            } else {
                /** @var TemplateService $templateService */
                $templateService = pluginApp(TemplateService::class);
                $templateService->forceNoIndex(true);
            }
        }
    }

    /**
     * After the request is processed, do nothing here.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        return $response;
    }
}
