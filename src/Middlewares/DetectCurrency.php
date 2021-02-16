<?php

namespace IO\Middlewares;

use IO\Services\CheckoutService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class DetectCurrency
 *
 * Set currency from request, if necessary.
 *
 * @package IO\Middlewares
 */
class DetectCurrency extends Middleware
{
    // TODO: move check to the core after 5.0.X
    /**
     * @var string[] $allCurrencies List of all possible currencies
     */
    public static $allCurrencies = [
        'AED', 'ARS', 'AUD', 'BGN', 'BHD', 'BRL',
        'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'EUR',
        'GBP', 'HKD', 'HRK', 'HUF', 'IDR', 'INR',
        'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP',
        'PLN', 'QAR', 'RON', 'RUB', 'SEK', 'SGD',
        'THB', 'TRY', 'TWD', 'UAH', 'USD', 'VND', 'XCD', 'ZAR'];

    /**
     * Before the request is processed, the currency is changed, if necessary.
     *
     * Example request: ?currency=EUR or ?Currency=EUR
     *
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
            if (in_array($currency, $enabledCurrencies) || (array_pop($enabledCurrencies) == 'all' && in_array(strtoupper($currency), self::$allCurrencies))) {
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
