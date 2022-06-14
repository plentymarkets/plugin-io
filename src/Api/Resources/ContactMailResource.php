<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Helper\TemplateContainer;
use IO\Services\ContactMailService;
use IO\Services\NotificationService;
use IO\Services\TemplateConfigService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class ContactMailResource
 *
 * Resource class for the route `io/customer/contact/mail`.
 * @package IO\Api\Resources
 */
class ContactMailResource extends ApiResource
{
    /**
     * @var ContactMailService $contactMailService Instance of the ContactMailService.
     */
    private $contactMailService;

    /**
     * @var TemplateConfigService $templateConfigService Instance of the TemplateConfigService
     */
    private $templateConfigService;

    /**
     * ContactMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ContactMailService $contactMailService
     * @param TemplateConfigService $templateConfigService
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        ContactMailService $contactMailService,
        TemplateConfigService $templateConfigService
    ) {
        parent::__construct($request, $response);
        $this->contactMailService = $contactMailService;
        $this->templateConfigService = $templateConfigService;
    }

    /**
     * Create a contact mail of the given data.
     * @return Response
     */
    public function store(): Response
    {
        // Honeypot check
        if (strlen($this->request->get('data')['username']['value'])) {
            return $this->response->create(true, ResponseCode::OK);
        }

        $mailTemplate = TemplateContainer::get('tpl.mail.contact')->getTemplate();

        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null), true)) {
            /**
             * @var NotificationService $notificationService
             */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);

            return $this->response->create("", ResponseCode::BAD_REQUEST);
        }

        $response = $this->contactMailService->sendMail(
            $mailTemplate,
            $this->request->all()
        );

        if ($response) {
            return $this->response->create($response, ResponseCode::CREATED);
        }

        return $this->response->create($response, ResponseCode::BAD_REQUEST);
    }

    /**
     * Verify a reCAPTCHA token
     * @param string $secret The reCAPTCHA secret.
     * @param string $token The reCAPTCHA token.
     * @return bool Validation result for the reCAPTCHA.
     */
    public function verifyRecaptcha($secret, $token)
    {
        if (!strlen($secret)) {
            return true;
        } elseif (!strlen($token)) {
            return false;
        }

        $params = [
            'secret' => $secret,
            'response' => $token
        ];

        $options = array(
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params)
        );

        $ch = curl_init();

        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($content, true);

        return is_array($result)
            && $result['success']
            && (!array_key_exists('score', $result)
                || $result['score'] >= $this->templateConfigService->get('global.google_recaptcha_threshold')
            );
    }
}
