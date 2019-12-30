<?php

namespace IO\Middlewares;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class HandleNewsletter extends Middleware
{
    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);

        $authString = $request->get('authString', '');
        $newsletterEmailId = $request->get('newsletterEmailId', 0);

        if (strlen($authString) && (int)$newsletterEmailId > 0 && RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_IN)) {
            AuthGuard::redirect('/newsletter/subscribe/' . $authString . '/' . $newsletterEmailId);
        }

        $orderShow = $request->get('OrderShow', '');
        if (strlen($orderShow) && $orderShow == 'CancelNewsletter' && in_array(RouteConfig::NEWSLETTER_OPT_OUT,
                RouteConfig::getEnabledRoutes())) {
            $folderId = $request->get('folderId', 0);
            AuthGuard::redirect($shopUrls->newsletterOptOut, ['folderId' => $folderId]);
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