<?php

namespace IO\Middlewares;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class HandleNewsletter
 *
 * Redirect to newsletter subscribe page, if necessary.
 *
 * @package IO\Middlewares
 */
class HandleNewsletter extends Middleware
{
    /**
     * Before the request is processed, check the request and redirect to newsletter page, if necessary.
     *
     * Example request: ?authString=AUTHSTRING&newsletterEmailId=EMAILDIRID
     *
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
