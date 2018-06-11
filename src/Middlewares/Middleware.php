<?php // strict

namespace IO\Middlewares;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Controllers\StaticPagesController;
use IO\Services\CheckoutService;

class Middleware extends \Plenty\Plugin\Middleware
{
    public function before(Request $request )
    {
        $loginToken = $request->get('token', '');
        if(strlen($loginToken))
        {
            /** @var ContactAuthenticationRepositoryContract $authRepo */
            $authRepo = pluginApp(ContactAuthenticationRepositoryContract::class);
            $authRepo->authenticateWithToken($loginToken);
        }
        
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

        return $response;
    }
}