<?php //strict

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerPasswordResetService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

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
        
        /**
         * @var CustomerPasswordResetService $customerPasswordResetService
         */
        $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);
        $response = $customerPasswordResetService->resetPassword($email);

        if($response === false)
        {
            return $this->response->create($response, ResponseCode::BAD_REQUEST);
        }

        return $this->response->create($response, ResponseCode::OK);
    }
    
}


