<?php //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Request;
use Illuminate\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CustomerService;

class CustomerPasswordResource extends ApiResource
{
	/**
	 * @var CustomerService
	 */
	private $customerService;
	
	public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
	{
		parent::__construct($request, $response);
		$this->customerService = $customerService;
	}
	
	public function store():Response
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
