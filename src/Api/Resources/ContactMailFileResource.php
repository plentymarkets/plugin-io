<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Services\ContactMailService;
use IO\Services\NotificationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class ContactMailFileResource
 * @package IO\Api\Resources
 */
class ContactMailFileResource extends ApiResource
{
    private $contactMailService;
    
    /**
     * ContactMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        ContactMailService $contactMailService
    ) {
        parent::__construct($request, $response);
        $this->contactMailService = $contactMailService;
    }
    
    /**
     * @return Response
     */
    public function store(): Response
    {
        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null))) {
            /**
             * @var NotificationService $notificationService
             */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);
        
            return $this->response->create([], ResponseCode::BAD_REQUEST);
        }
        
        if (isset($_FILES['fileData'])) {
            $response = $this->contactMailService->uploadFile($_FILES['fileData']);
            
            if ($response) {
                return $this->response->create(['fileKey' => $response], ResponseCode::CREATED);
            }
        }
    
        return $this->response->create(['fileKey' => null], ResponseCode::BAD_REQUEST);
    }
}
