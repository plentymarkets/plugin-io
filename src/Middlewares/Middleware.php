<?php // strict

namespace IO\Middlewares;

use IO\Api\ResponseCode;
use IO\Constants\SessionStorageKeys;
use IO\Controllers\CheckoutController;
use IO\Helper\RouteConfig;
use IO\Services\CountryService;
use IO\Services\TemplateService;
use IO\Services\WebstoreConfigurationService;
use IO\Services\TemplateConfigService;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Controllers\StaticPagesController;
use IO\Services\CheckoutService;
use IO\Services\LocalizationService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use IO\Guards\AuthGuard;

class Middleware extends \Plenty\Plugin\Middleware
{
    public function before(Request $request)
    {
        /** @var SessionStorageService $sessionService */
        $sessionService  = pluginApp(SessionStorageService::class);
        
        $loginToken = $request->get('token', '');
        if(strlen($loginToken))
        {
            /** @var ContactAuthenticationRepositoryContract $authRepo */
            $authRepo = pluginApp(ContactAuthenticationRepositoryContract::class);
            $authRepo->authenticateWithToken($loginToken);
        }

        $splittedURL     = explode('/', $request->get('plentyMarkets'));
        $lang            = $splittedURL[0];
        $webstoreService = pluginApp(WebstoreConfigurationService::class);
        $webstoreConfig  = $webstoreService->getWebstoreConfig();

        if (($lang == null || strlen($lang) != 2 || !in_array($lang, $webstoreConfig->languageList)) && strpos(end($splittedURL), '.') === false)
        {
            if($sessionService->getLang() != $webstoreConfig->defaultLanguage)
            {
                $service = pluginApp(LocalizationService::class);
                $service->setLanguage($webstoreConfig->defaultLanguage);

                 /** @var TemplateConfigService $templateConfigService */
                $templateConfigService = pluginApp(TemplateConfigService::class);
                $enabledCurrencies = explode(', ',  $templateConfigService->get('currency.available_currencies') );
                $currency = $webstoreConfig->defaultCurrencyList[$webstoreConfig->defaultLanguage];
                if(!is_null($currency) && (in_array($currency, $enabledCurrencies) || array_pop($enabledCurrencies) == 'all'))
                {
                    /** @var CheckoutService $checkoutService */
                    $checkoutService = pluginApp(CheckoutService::class);
                    $checkoutService->setCurrency( $currency );
                }
            }
        }

        $currency = $request->get('currency', null);
        $currency = !is_null($currency) ? $currency : $request->get('Currency', null);


        if ( $currency != null )
        {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $enabledCurrencies = explode(', ',  $templateConfigService->get('currency.available_currencies') );

            if(in_array($currency, $enabledCurrencies) || array_pop($enabledCurrencies) == 'all')
            {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setCurrency( $currency );
            }
            else
            {
                /** @var TemplateService $templateService */
                $templateService = pluginApp(TemplateService::class);
                $templateService->forceNoIndex(true);
            }
        }

        $shipToCountry = $request->get('ShipToCountry', null);
        if ( $shipToCountry != null )
        {
            /** @var CountryService $countryService */
            $countryService = pluginApp(CountryService::class);
            $country = $countryService->getCountryById( $shipToCountry );
            if(!is_null($country) && $country->active)
            {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setShippingCountryId( $shipToCountry );
            }
            else
            {
                /** @var TemplateService $templateService */
                $templateService = pluginApp(TemplateService::class);
                $templateService->forceNoIndex(true);
            }
        }

        $referrerId = $request->get('ReferrerID', null);
        if(!is_null($referrerId))
        {
            /** @var Checkout $checkout */
            $checkout = pluginApp(Checkout::class);
            $checkout->setBasketReferrerId($referrerId);
        }

        $authString = $request->get('authString', '');
        $newsletterEmailId = $request->get('newsletterEmailId', 0);

        if(strlen($authString) && (int)$newsletterEmailId > 0 && RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_IN))
        {
            AuthGuard::redirect('/newsletter/subscribe/'.$authString.'/'.$newsletterEmailId);
        }

        $orderShow = $request->get('OrderShow', '');
        if(strlen($orderShow) && $orderShow == 'CancelNewsletter' && RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_OUT))
        {
            AuthGuard::redirect('/newsletter/unsubscribe');
        }

        if ( RouteConfig::isActive(RouteConfig::SEARCH) && $request->get('ActionCall') == 'WebActionArticleSearch' )
        {
            AuthGuard::redirect('/search', ['query' => $request->get('Params')['SearchParam']]);
        }
        
        /** @var CheckoutService $checkoutService */
        $checkoutService = pluginApp(CheckoutService::class);
        $checkoutService->setReadOnlyCheckout($request->get('readonlyCheckout',0) == 1);
    }

    public function after(Request $request, Response $response):Response
    {
        if ($response->status() == ResponseCode::NOT_FOUND) {
            /** @var StaticPagesController $controller */
            $controller = pluginApp(StaticPagesController::class);

            $response = $response->make(
                $controller->showPageNotFound(),
                ResponseCode::NOT_FOUND
            );

            $response->forceStatus(ResponseCode::NOT_FOUND);
            return $response;
        }

        return $response;
    }
}
