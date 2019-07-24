<?php

namespace IO\Api\Resources;

use IO\Helper\TemplateContainer;
use IO\Extensions\Functions\ExternalContent;
use IO\Services\TemplateConfigService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ContactMailService;

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
        $mailTemplate = TemplateContainer::get('tpl.mail.contact')->getTemplate();

        $recaptchaToken = $this->request->get('recaptchaToken', null);
        $recaptchaSecret = $this->templateConfigService->get('global.google_recaptcha_secret');

        if ( !$this->verifyRecaptcha($recaptchaSecret, $recaptchaToken) )
        {
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
