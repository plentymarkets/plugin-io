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
     */
    public function after(Request $request, Response $response)
    {
        if($request->has('generateAccessKeySuccess')) {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $successStatus = $request->get('generateAccessKeySuccess');
            if($successStatus == 1) {
                $notificationService->success('success', 1401);
            } else {
                $notificationService->error('fail', 1402);
            }
        }
        return $response;
    }
}
