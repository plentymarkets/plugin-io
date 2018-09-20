<?php // strict

namespace IO\Middlewares;

use IO\Api\ResponseCode;
use IO\Services\WebstoreConfigurationService;

use Plenty\Plugin\ConfigRepository;
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

        if ($lang == null || strlen($lang) != 2 || !in_array($lang, $webstoreConfig->languageList))
        {
            $sessionService  = pluginApp(SessionStorageService::class);

            if($sessionService->getLang() != $webstoreConfig->defaultLanguage)
            {
                $service = pluginApp(LocalizationService::class);
                $service->setLanguage($webstoreConfig->defaultLanguage);
            }
        }

        $currency = $request->get('currency', null);

        if ( $currency != null )
        {
            /** @var ConfigRepository $config */
            $config = pluginApp(ConfigRepository::class);
            $enabledCurrencies = explode(', ',  $config->get('Ceres.currency.available_currencies') );

            if(in_array($currency, $enabledCurrencies) || array_pop($enabledCurrencies) == 'all')
            {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setCurrency( $currency );
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
        
        if(strlen($authString) && (int)$newsletterEmailId > 0)
        {
            AuthGuard::redirect('/newsletter/subscribe/'.$authString.'/'.$newsletterEmailId);
        }
        
        $orderShow = $request->get('OrderShow', '');
        if(strlen($orderShow) && $orderShow == 'CancelNewsletter')
        {
            AuthGuard::redirect('/newsletter/unsubscribe');
        }

        $this->checkForCallistoSearchURL($request);
    }

    public function after(Request $request, Response $response):Response
    {
        if ($response->content() == '') {
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

    private function checkForCallistoSearchURL(Request $request)
    {
        $config = pluginApp(ConfigRepository::class);
        $enabledRoutes = explode(", ",  $config->get("IO.routing.enabled_routes") );

        if ( (in_array("search", $enabledRoutes) || in_array("all", $enabledRoutes)) &&
             $request->get('ActionCall') == 'WebActionArticleSearch' )
        {
            AuthGuard::redirect('/search', ['query' => $request->get('Params')['SearchParam']]);
        }
    }
}