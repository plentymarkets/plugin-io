<?php

namespace IO\Middlewares;

use IO\Services\NotificationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class ClearNotifications
 *
 * Clear notifications if necessary.
 *
 * @package IO\Middlewares
 */
class ClearNotifications extends Middleware
{
    /**
     * @var bool $CLEAR_NOTIFICATIONS Force clear notifications status.
     */
    public static $CLEAR_NOTIFICATIONS = false;

    /**
     * Before the request is processed, do nothing here.
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
    }

    /**
     * After the request is processed, check if the notifications should be cleared.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        if (self::$CLEAR_NOTIFICATIONS) {
             /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->clearNotifications();
        }

        return $response;
    }
}
