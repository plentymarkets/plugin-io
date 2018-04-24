<?php // strict

namespace IO\Middlewares;

use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\ContentCache\Contracts\ContentCacheRepositoryContract;
use IO\Controllers\StaticPagesController;
use IO\Services\CheckoutService;
//use Illuminate\Support\Facades\Cache;

class Middleware extends \Plenty\Plugin\Middleware
{
    public function before(Request $request )
    {
        $currency = $request->get('currency', null);
        if ( $currency != null )
        {
            /** @var CheckoutService $checkoutService */
            $checkoutService = pluginApp(CheckoutService::class);
            $checkoutService->setCurrency( $currency );
        }

        $referrerId = $request->get('ReferrerID', null);
        if(!is_null($referrerId))
        {
            /** @var Checkout $checkout */
            $checkout = pluginApp(Checkout::class);
            $checkout->setBasketReferrerId($referrerId);
        }
        
        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        
        if($sessionStorageService->getLang() !== $webstoreConfigService->getDefaultLanguage())
        {
            setcookie('ceres_lang', $sessionStorageService->getLang());
        }
    }

    public function after(Request $request, Response $response):Response
    {
        if ($response->content() == '') {
            /** @var StaticPagesController $controller */
            $controller = pluginApp(StaticPagesController::class);

            $response = $response->make(
                $controller->showPageNotFound(),
                404
            );

            $response->forceStatus(404);
            return $response;
        }
        
        $cacheKey = $_SERVER['REQUEST_URI'];
        
        /** @var ContentCacheRepositoryContract $contentCacheRepo */
        $contentCacheRepo = pluginApp(ContentCacheRepositoryContract::class);
        $contentCacheRepo->saveCacheEntry($cacheKey, $response->content());
        
        //$result = Cache::store('redis_content_cache')->get($cacheKey);

        return $response;
    }
}