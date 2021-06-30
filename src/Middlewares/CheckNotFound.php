<?php

namespace IO\Middlewares;

use IO\Api\ResponseCode;
use IO\Controllers\CategoryController;
use IO\Controllers\StaticPagesController;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class CheckNotFound
 *
 * Return the template for "Page not found", if necessary.
 *
 * @package IO\Middlewares
 */
class CheckNotFound extends Middleware
{
    /**
     * @var bool $FORCE_404 Force page not found status.
     */
    public static $FORCE_404 = false;

    /**
     * Before the request is processed, do nothing here.
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
    }

    /**
     * After the request is processed, check if "Page not found" template should be returned.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \ErrorException
     */
    public function after(Request $request, Response $response)
    {
        if ($response->status() == ResponseCode::NOT_FOUND) {
            $routeActive = RouteConfig::isActive(RouteConfig::PAGE_NOT_FOUND);
            $sbCategoryId = RouteConfig::getCategoryId(RouteConfig::PAGE_NOT_FOUND);

            if ($routeActive || $sbCategoryId > 0 || self::$FORCE_404) {
                if ($sbCategoryId > 0) {
                    /** @var CategoryController $controller */
                    $controller = pluginApp(CategoryController::class);
                    $content = $controller->showCategoryById($sbCategoryId);
                } else {
                    /** @var StaticPagesController $controller */
                    $controller = pluginApp(StaticPagesController::class);
                    $content = $controller->showPageNotFound();
                }

                $response = $response->make(
                    $content,
                    ResponseCode::NOT_FOUND
                );
                $response->forceStatus(ResponseCode::NOT_FOUND);
            }
        }

        return $response;
    }
}
