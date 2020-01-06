<?php

namespace IO\Middlewares;

use IO\Extensions\Constants\ShopUrls;
use IO\Services\CheckoutService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class DetectReadonlyCheckout extends Middleware
{
    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);

        if ($request->has('readonlyCheckout') || $request->getRequestUri() !== $shopUrls->checkout) {
            /** @var CheckoutService $checkoutService */
            $checkoutService = pluginApp(CheckoutService::class);
            $checkoutService->setReadOnlyCheckout($request->get('readonlyCheckout', 0) == 1);
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