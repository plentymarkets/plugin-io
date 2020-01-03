<?php

namespace IO\Middlewares;

use IO\Services\CheckoutService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class DetectCurrency extends Middleware
{
    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        $currency = $request->get('currency', null);
        $currency = !is_null($currency) ? $currency : $request->get('Currency', null);

        if (!is_null($currency)) {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $enabledCurrencies = explode(', ', $templateConfigService->get('currency.available_currencies'));

            if (in_array($currency, $enabledCurrencies) || array_pop($enabledCurrencies) == 'all') {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setCurrency($currency);
            } else {
                /** @var TemplateService $templateService */
                $templateService = pluginApp(TemplateService::class);
                $templateService->forceNoIndex(true);
            }
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        return $response;
    }
}
