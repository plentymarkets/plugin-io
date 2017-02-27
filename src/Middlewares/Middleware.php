<?php // strict

namespace IO\Middlewares;

use IO\Controllers\StaticPagesController;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

class Middleware extends \Plenty\Plugin\Middleware
{

    public function before(Request $request)
    {

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