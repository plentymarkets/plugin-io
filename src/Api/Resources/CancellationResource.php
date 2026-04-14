<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Services\NotificationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class CancellationResource
 *
 * Resource class for the route `io/cancellation`.
 * @package IO\Api\Resources
 */
class CancellationResource extends ApiResource
{
    /**
     * CancellationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }

    /**
     * Submit a cancellation request.
     * @return Response
     */
    public function store(): Response
    {
        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null), true)) {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);

            return $this->response->create('', ResponseCode::BAD_REQUEST);
        }

        $payload = $this->request->all();

        // TODO: call shared cancellation service/repository

        return $this->response->create([], ResponseCode::OK);
    }
}
