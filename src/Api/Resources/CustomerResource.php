<?php //strict

namespace IO\Api\Resources;

use Plenty\Modules\Account\Contact\Models\Contact;
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
     * @return Response
     */
	public function index():Response
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
     * @return Response
     */
	public function store():Response
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
  
  
		if(!$contact instanceof Contact)
        {
            $this->response->error(1, '');
            return $this->response->create($contact, ResponseCode::IM_USED);
        }
        
        return $this->index();
	}
}
