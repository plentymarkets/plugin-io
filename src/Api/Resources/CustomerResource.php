<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;

/**
 * Class CustomerResource
 * @package IO\Api\Resources
 */
class CustomerResource extends ApiResource
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

    /**
     * CustomerResource constructor.
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
     * Get the contact
     * @return BaseResponse
     */
	public function index():BaseResponse
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

    /**
     * Save the contact
     * @return BaseResponse
     */
	public function store():BaseResponse
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
