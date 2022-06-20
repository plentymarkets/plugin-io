<?php

namespace IO\Middlewares;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use IO\Services\OrderService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class HandleOrderPreviewUrl
 *
 * Redirect to order confirmation, if necessary.
 *
 * @package IO\Middlewares
 */
class HandleOrderPreviewUrl extends Middleware
{
    /**
     * Before the request is processed, check the request and redirect to order confirmation page, if necessary.
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        $orderId = $request->get('id', 0);
        $orderAccessKey = $request->get('ak', '');

        if (strlen($orderAccessKey) && (int)$orderId > 0) {
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);

            /** @var OrderService $orderService */
            $orderService = pluginApp(OrderService::class);
            $confirmationUrl = $orderService->getConfirmationUrl($shopUrls->confirmation, $orderId, $orderAccessKey);
            AuthGuard::redirect($confirmationUrl);
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

