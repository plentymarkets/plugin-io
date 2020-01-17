<?php

namespace IO\Middlewares;

use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class ClearNotifications extends Middleware
{
    public static $CLEAR_NOTIFICATIONS = false;

    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        if (self::$CLEAR_NOTIFICATIONS) {
             /** @var SessionStorageRepositoryContract $sessionStorageRepository */
            $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);
            $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::NOTIFICATIONS, json_encode([]));
        }

        return $response;
    }
}
