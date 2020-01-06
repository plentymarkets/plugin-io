<?php

namespace IO\Middlewares;

use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class DetectReferrer extends Middleware
{
    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        $referrerId = $request->get('ReferrerID', null);
        if (!is_null($referrerId)) {
            /** @var Checkout $checkout */
            $checkout = pluginApp(Checkout::class);
            $checkout->setBasketReferrerId($referrerId);
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