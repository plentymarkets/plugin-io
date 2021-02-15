<?php

namespace IO\Middlewares;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class HandleOrderPreviewUrl
 *
 * Redirect to confirmation if necessary
 *
 * @package IO\Middlewares
 */
class HandleOrderPreviewUrl extends Middleware
{
    /**
     * Before the request is processed, check the request and redirect to confirmation page if necessary
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);

        // access 'Kaufabwicklungslink'
        if (RouteConfig::isActive(RouteConfig::CONFIRMATION)) {
            $orderId = $request->get('id', 0);
            $orderAccessKey = $request->get('ak', '');

            if (strlen($orderAccessKey) && (int)$orderId > 0) {
                $confirmationRoute = $shopUrls->confirmation . '/' . $orderId . '/' . $orderAccessKey;
                AuthGuard::redirect($confirmationRoute);
            }
        } elseif (in_array(RouteConfig::CONFIRMATION, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0) {
            $orderId = $request->get('id', 0);
            $orderAccessKey = $request->get('ak', '');

            if (strlen($orderAccessKey) && (int)$orderId > 0) {
                $confirmationRoute = $shopUrls->confirmation . '?orderId=' . $orderId . '&accessKey=' . $orderAccessKey;
                AuthGuard::redirect($confirmationRoute);
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

