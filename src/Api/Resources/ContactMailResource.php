<?php

namespace IO\Api\Resources;

use IO\Helper\TemplateContainer;
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
    
    /**
     * ContactMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response, ContactMailService $contactMailService)
    {
        parent::__construct($request, $response);
        $this->contactMailService = $contactMailService;
    }
    
    public function store():Response
    {
        $mailTemplate = TemplateContainer::get('tpl.mail.contact')->getTemplate();

        $response = $this->contactMailService->sendMail(
            $mailTemplate,
            $this->request->get('recipient', null),
            $this->request->get('subject', ''),
            $this->request->get('cc', []),
            $this->request->get('replyTo', null),
            $this->request->get('mailData',[])
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
}