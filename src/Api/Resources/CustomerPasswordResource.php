<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;

/**
 * Class CustomerPasswordResource
 * @package IO\Api\Resources
 */
class CustomerPasswordResource extends ApiResource
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

    /**
     * CustomerPasswordResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CustomerService $customerService
     */
	public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
	{
		parent::__construct($request, $response);
		$this->customerService = $customerService;
	}

    /**
     * Set the password for the contact
     * @return Response
     */
	public function store():Response
	{
        $newPassWord = $this->request->get('password', null);
	    $contactId = $this->request->get('contactId', 0);
        $hash = $this->request->get('hash', null);
	    
		if($newPassWord !== null)
		{
			$result = $this->customerService->updatePassword($newPassWord, $contactId, $hash);
			return $this->response->create(null, ResponseCode::OK);
		}
		$this->response->error(0, "Missing parameter: password");
		return $this->response->create(null, ResponseCode::BAD_REQUEST);
	}

}
