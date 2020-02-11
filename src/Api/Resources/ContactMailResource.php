<?php

namespace IO\Api\Resources;

use IO\Helper\ReCaptcha;
use IO\Helper\TemplateContainer;
use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;
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

    /** @var TemplateConfigRepositoryContract */
    private $templateConfigRepository;

    /**
     * ContactMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        ContactMailService $contactMailService,
        TemplateConfigRepositoryContract $templateConfigRepository
    ) {
        parent::__construct($request, $response);
        $this->contactMailService = $contactMailService;
        $this->templateConfigRepository = $templateConfigRepository;
    }

    public function store(): Response
    {
        // Honeypot check
        if (strlen($this->request->get('data')['username']['value'])) {
            return $this->response->create(true, ResponseCode::OK);
        }

        $mailTemplate = TemplateContainer::get('tpl.mail.contact')->getTemplate();

        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null))) {
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

        return $result['success']
            && (!array_key_exists('score', $result)
                || $result['score'] >= $this->templateConfigRepository->get('global.google_recaptcha_threshold')
            );
    }
}
