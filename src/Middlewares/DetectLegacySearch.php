<?php

namespace IO\Middlewares;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class DetectLegacySearch
 *
 * Redirect to search page, if necessary.
 *
 * @package IO\Middlewares
 */
class DetectLegacySearch extends Middleware
{
    /**
     * Before the request is processed, check the request and redirect to search, if necessary.
     *
     * Example request: ?ActionCall=WebActionArticleSearch&Params[SearchParams]=SEARCHQUERY
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        if (RouteConfig::isActive(RouteConfig::SEARCH) && $request->get('ActionCall') == 'WebActionArticleSearch') {
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);
            AuthGuard::redirect($shopUrls->search, ['query' => $request->get('Params')['SearchParam'] ?? '']);
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
