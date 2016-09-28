<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseReponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CustomerService;
use LayoutCore\Builder\Order\AddressType;

class CustomerAddressResource extends ApiResource
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
	
	private function getAddressType():int
	{
		return (INT)$this->request->get("typeId", null);
	}
	
	public function index():BaseReponse
	{
		$type      = $this->getAddressType();
		$addresses = $this->customerService->getAddresses($type);
		return $this->response->create($addresses, ResponseCode::OK);
	}
	
	public function store():BaseReponse
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
	
	public function update(string $addressId):BaseReponse
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
	
	public function destroy(string $addressId):BaseReponse
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
