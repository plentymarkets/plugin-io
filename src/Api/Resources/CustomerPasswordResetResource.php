<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerPasswordResetService;

/**
 * Class CustomerPasswordResetResource
 * @package IO\Api\Resources
 */
class CustomerPasswordResetResource extends ApiResource
{
    /**
     * CustomerPasswordResetResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Set the password for the contact
     * @return Response
     */
    public function store():Response
    {
        $email = $this->request->get('email', '');
        $template = $this->request->get('template', '');
        $mailSubject = $this->request->get('subject', '');
        
        /**
         * @var CustomerPasswordResetService $customerPasswordResetService
         */
        $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);
        $response = $customerPasswordResetService->resetPassword($email, $template, $mailSubject);
        return $this->response->create($response, ResponseCode::OK);
    }
    
}


