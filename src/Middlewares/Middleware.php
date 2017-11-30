<?php // strict

namespace IO\Middlewares;

use IO\Controllers\StaticPagesController;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Services\CheckoutService;

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

    }

    public function after(Request $request, Response $response):Response
    {
        if ($response->content() == '') {
            /** @var StaticPagesController $controller */
            $controller = pluginApp(StaticPagesController::class);

            return $response->make(
                $controller->showPageNotFound()
            );
        }

        return $response;
    }
}