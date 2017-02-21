<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;
use IO\Builder\Order\AddressType;

/**
 * Class CustomerAddressResource
 * @package IO\Api\Resources
 */
class CustomerAddressResource extends ApiResource
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

    /**
     * CustomerAddressResource constructor.
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
     * Get the address type from the request
     * @return int
     */
	private function getAddressType():int
	{
		return (INT)$this->request->get("typeId", null);
	}

    /**
     * Get an address by type
     * @return Response
     */
	public function index():Response
	{
		$type      = $this->getAddressType();
		$addresses = $this->customerService->getAddresses($type);
		return $this->response->create($addresses, ResponseCode::OK);
	}

    /**
     * Create an address with the given type
     * @return Response
     */
	public function store():Response
	{
		$type = $this->getAddressType();
		if($type === 0)
		{
			$this->response->error(0, "Missing type id.");
			return $this->response->create(null, ResponseCode::BAD_REQUEST);
		}
		$address = $this->customerService->createAddress($this->request->all(), $type);
		return $this->response->create($address, ResponseCode::CREATED);
	}

    /**
     * Update the address with the given ID
     * @param string $addressId
     * @return Response
     */
	public function update(string $addressId):Response
	{
		$type = $this->getAddressType();
		if($type === 0)
		{
			$this->response->error(0, "Missing type id.");
			return $this->response->create(null, ResponseCode::BAD_REQUEST);
		}

		$addressId = (int)$addressId;
		$address   = $this->customerService->updateAddress($addressId, $this->request->all(), $type);
		return $this->response->create($address, ResponseCode::OK);
	}

    /**
     * Delete the address with the given ID
     * @param string $addressId
     * @return Response
     */
	public function destroy(string $addressId):Response
	{
		$type = $this->getAddressType();
		if($type === 0)
		{
			$this->response->error(0, "Missing type id.");
			return $this->response->create(null, ResponseCode::BAD_REQUEST);
		}

		$addressId = (int)$addressId;
		$this->customerService->deleteAddress($addressId, $type);
		return $this->index();
	}
}
