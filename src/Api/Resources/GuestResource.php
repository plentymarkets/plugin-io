<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\SessionStorageService;
use IO\Constants\SessionStorageKeys;


class GuestResource extends ApiResource
{
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    public function store():Response
    {
        $email = $this->request->get('email', '');
    
        /**
         * @var SessionStorageService $sessionStorage
         */
        $sessionStorage = pluginApp(SessionStorageService::class);
        $sessionStorage->setSessionValue(SessionStorageKeys::GUEST_EMAIL, $email);
        
        return $this->response->create($email, ResponseCode::OK);
    }
}
