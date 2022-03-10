<?php

namespace IO\Api\Resources;

use IO\Services\CustomerNewsletterService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Services\NotificationService;

/**
 * Class CustomerNewsletterResource
 *
 * Resource class for the route `io/customer/newsletter`.
 * @package IO\Api\Resources
 */
class CustomerNewsletterResource extends ApiResource
{
    /**
     * @var CustomerNewsletterService $newsletterService Instance of the CustomerNewsletterService.
     */
    private $newsletterService;

    /**
     * CustomerNewsletterResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CustomerNewsletterService $newsletterService
     */
    public function __construct(Request $request, ApiResponse $response, CustomerNewsletterService $newsletterService)
    {
        parent::__construct($request, $response);

        $this->newsletterService = $newsletterService;
    }

    /**
     * Subscribe an email address to a newsletter.
     * @return Response
     */
    public function store(): Response
    {
        // Honeypot check
        if(strlen($this->request->get('honeypot')))
        {
            // We can potentially expand on the honeypot handling with sending reports to spam protect services
            return $this->response->create(['containsHoneypot' => true], ResponseCode::OK);
        }

        if (!ReCaptcha::verify($this->request->get('recaptcha', null))) {
            /**
            * @var NotificationService $notificationService
            */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);

            return $this->response->create('', ResponseCode::BAD_REQUEST);
        }

        $email = $this->request->get('email', '');
        $firstName = $this->request->get('firstName', '');
        $lastName = $this->request->get('lastName', '');
        $emailFolder = $this->request->get('emailFolder', 0);

        $filter_url_pattern = '/[.:\/\d]/';
        if (preg_match_all($filter_url_pattern, $firstName) > 0 || preg_match_all($filter_url_pattern, $lastName) > 0 ) {
            return $this->response->create('', ResponseCode::BAD_REQUEST);
        }

        $this->newsletterService->saveNewsletterData($email, $emailFolder, $firstName, $lastName);

        return $this->response->create($email, ResponseCode::OK);
    }

    /**
     * Unsubscribe an email address from a newsletter.
     * @param string $selector Email address.
     * @return Response
     */
    public function destroy(string $selector): Response
    {
        // Honeypot check
        if(strlen($this->request->get('honeypot')))
        {
            return $this->response->create(true, ResponseCode::OK);
        }

        $emailFolder = $this->request->get('emailFolder', 0);

        $success = $this->newsletterService->deleteNewsletterDataByEmail($selector, (int) $emailFolder);

        return $this->response->create($success, ResponseCode::OK);
    }
}
