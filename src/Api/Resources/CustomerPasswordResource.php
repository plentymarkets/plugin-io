<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CustomerService;

/**
 * Class CustomerPasswordResource
 * @package LayoutCore\Api\Resources
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
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$password = $this->request->get("password", null);
		if($password !== null)
		{
			$this->customerService->updateContact([
				                                      "changeOnlyPassword" => true,
				                                      "password"           => $password
			                                      ]);
			return $this->response->create(null, ResponseCode::OK);
		}
		$this->response->error(0, "Missing parameter: password");
		return $this->response->create(null, ResponseCode::BAD_REQUEST);
	}

}
