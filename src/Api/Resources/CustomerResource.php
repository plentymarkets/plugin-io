<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseReponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CustomerService;

class CustomerResource extends ApiResource
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
	
	public function index():BaseReponse
	{
		$customer = null;
		$contact  = $this->customerService->getContact();
		if($contact !== null)
		{
			$customer = [
				"contact"   => $contact,
				"addresses" => $this->customerService->getAddresses()
			];
		}
		
		return $this->response->create($customer, ResponseCode::OK);
	}
	
	public function store():BaseReponse
	{
		$contactData         = $this->request->get("contact", null);
		$billingAddressData  = $this->request->get("billingAddress", []);
		$deliveryAddressData = $this->request->get("deliveryAddress", []);
		
		if($contactData === null || !is_array($contactData))
		{
			$this->response->error(0, "Missing contact data or unexpected format.");
			return $this->response->create(null, ResponseCode::BAD_REQUEST);
		}
		
		if(!is_array($billingAddressData) || !is_array($deliveryAddressData))
		{
			$this->response->error(0, "Unexpected address format.");
			return $this->response->create(null, ResponseCode::BAD_REQUEST);
		}
		
		if(count($billingAddressData) === 0)
		{
			$billingAddressData = null;
		}
		
		if(count($deliveryAddressData) === 0)
		{
			$deliveryAddressData = null;
		}
		
		$contact = $this->customerService->registerCustomer(
			$contactData,
			$billingAddressData,
			$deliveryAddressData
		);
		
		return $this->index();
	}
}
