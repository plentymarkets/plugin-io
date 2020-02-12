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
 * @package IO\Api\Resources
 */
class ContactMailResource extends ApiResource
{
    private $contactMailService;

    private $templateConfigService;

    /**
     * ContactMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        ContactMailService $contactMailService,
        TemplateConfigService $templateConfigService)
    {
        parent::__construct($request, $response);
        $this->contactMailService = $contactMailService;
        $this->templateConfigService = $templateConfigService;
    }

    public function store():Response
    {
        // Honeypot check

        if(strlen($this->request->get('data')['username']['value']))
        {
            return $this->response->create(true, ResponseCode::OK);
        }

        $mailTemplate = TemplateContainer::get('tpl.mail.contact')->getTemplate();

        if( !ReCaptcha::verify($this->request->get('recaptchaToken', null)) )
        {
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

        if($response)
        {
            return $this->response->create($response, ResponseCode::CREATED);
        }
        else
        {
            return $this->response->create($response, ResponseCode::BAD_REQUEST);
        }

    }

    public function verifyRecaptcha( $secret, $token )
    {
        if ( !strlen( $secret ) )
        {
            return true;
        }
        else if ( !strlen( $token ) )
        {
            return false;
        }

        $params = [
            "secret" => $secret,
            "response" => $token
        ];
        $options = array(
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params)
        );

        $ch = curl_init();

        foreach($options as $option => $value)
        {
            curl_setopt($ch, $option, $value);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($content, true);

        return $result["success"]
            && (!array_key_exists('score', $result)
                || $result['score'] >= $this->templateConfigService->get('global.google_recaptcha_threshold')
            );
    }
}
