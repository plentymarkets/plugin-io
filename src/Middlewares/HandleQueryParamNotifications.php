<?php

namespace IO\Middlewares;

use IO\Services\NotificationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
* Class HandleQueryParamNotifications
 *
 *
 *
 * @package IO\Middlewares
*/
class HandleQueryParamNotifications extends Middleware {
    /**
     *
     * Before the request is processed, do nothing here.
     *
     * @param Request $request
     * @return Request
     */
    public function before(Request $request) {
        return $request;
    }

    /**
     * After the request is processed, give out Notification for success or failure of sending mail.
     * Example request: ?generateAccessKeyStatus=1
     * @param Request $request
     * @param Response $response
     * @return Response
     * * @var NotificationService $notificationService
     */
    public function after(Request $request, Response $response)
    {
        $request = pluginApp(Request::class);
        if($request->has('generateAccessKeyStatus')) {
            $notificationService = pluginApp(NotificationService::class);
            $successStatus = $request->get('generateAccessKeyStatus');
            if($successStatus == 1) {
                $notificationService->success('Hat geplappt');
            } else {
                $notificationService->error('Fehlschlag');
            }
        }
        return $response;
    }
}