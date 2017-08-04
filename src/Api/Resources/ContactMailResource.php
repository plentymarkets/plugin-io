<?php

namespace IO\Api\Resources;

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
        $mailTemplate = $this->request->get('template', '');
        $contactData = $this->request->get('contactData',[]);
        
        if(strlen($mailTemplate) && count($contactData))
        {
            $this->contactMailService->sendMail($mailTemplate, $contactData);
        }
        
        return $this->response->create('', ResponseCode::CREATED);
    }
}