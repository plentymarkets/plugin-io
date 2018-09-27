<?php

namespace IO\Api\Resources;

use IO\Services\CustomerNewsletterService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

class CustomerNewsletterResource extends ApiResource
{
    /** @var CustomerNewsletterService */
    private $newsletterService;
    
    public function __construct(Request $request, ApiResponse $response, CustomerNewsletterService $newsletterService)
    {
        parent::__construct($request, $response);
        
        $this->newsletterService = $newsletterService;
    }
    
    public function store(): Response
    {
        $email = $this->request->get('email', '');
        $firstName = $this->request->get('firstName', '');
        $lastName = $this->request->get('lastName', '');
        
        $this->newsletterService->saveNewsletterData($email, $firstName, $lastName);
    
        return $this->response->create($email, ResponseCode::OK);
    }
}