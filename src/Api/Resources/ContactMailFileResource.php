<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Services\NotificationService;
use Plenty\Modules\Webshop\ContactForm\Contracts\ContactFormFileRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class ContactMailFileResource
 * @package IO\Api\Resources
 */
class ContactMailFileResource extends ApiResource
{
    /**
     * ContactMailFileResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(
        Request $request,
        ApiResponse $response
    ) {
        parent::__construct($request, $response);
    }

    /**
     * @return Response
     */
    public function store(): Response
    {
        /**
         * @var NotificationService $notificationService
         */
        $notificationService = pluginApp(NotificationService::class);

        if (!$this->hasValidFileSize()) {
            $notificationService->addNotificationCode(LogLevel::ERROR, 1);

            return $this->response->create([], ResponseCode::REQUEST_ENTITY_TOO_LARGE);
        }

        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null))) {
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);

            return $this->response->create([], ResponseCode::BAD_REQUEST);
        }

        $response = null;
        if (isset($_FILES['fileData'])) {
            /** @var ContactFormFileRepositoryContract $contactFormFileRepository */
            $contactFormFileRepository = pluginApp(ContactFormFileRepositoryContract::class);

            try {
                $response = $contactFormFileRepository->uploadFiles($_FILES['fileData']);
            } catch (\Exception $exception) {
                $notificationService->addNotificationCode(LogLevel::ERROR, 0);
            }

            if (!is_null($response)) {
                return $this->response->create(['fileKeys' => $response], ResponseCode::CREATED);
            }
        }

        return $this->response->create([], ResponseCode::BAD_REQUEST);
    }

    private function hasValidFileSize() {
        foreach ($_FILES as $file) {
            if ($file['error'][0] === UPLOAD_ERR_INI_SIZE || $file['error'][0] === UPLOAD_ERR_FORM_SIZE) {
                return false;
            }
        }

        return true;
    }
}
