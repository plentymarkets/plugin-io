<?php // strict

namespace IO\Middlewares;

use IO\Api\ResponseCode;
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
        
        /** @var ContentCacheRepositoryContract $contentCacheRepo */
        $contentCacheRepo = pluginApp(ContentCacheRepositoryContract::class);
        
        $contentCacheRepo->saveCacheEntry($_SERVER['REQUEST_URI'], $response->content());
        
        $responseContent = $response->content();
        $response = $response->make(
            $responseContent,
            ResponseCode::OK,
           ['plenty_cache' => $contentCacheRepo->setCacheCookie($_SERVER['REQUEST_URI'])]
        );
    
        //$response->header('plenty_cache', $contentCacheRepo->setCacheCookie($_SERVER['REQUEST_URI']));
        
        return $response;
    }
}