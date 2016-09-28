<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use LayoutCore\Builder\Order\AddressType;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Plugin\Application;
use LayoutCore\Helper\AbstractFactory;
use LayoutCore\Helper\UserSession;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use LayoutCore\Services\AuthenticationService;

class CustomerService
{
	/**
	 * @var ContactRepositoryContract
	 */
	private $contactRepository;
	/**
	 * @var ContactAddressRepositoryContract
	 */
	private $addressRepository;
	/**
	 * @var OrderRepositoryContract
	 */
	private $orderRepository;
	/**
	 * @var AuthenticationService
	 */
	private $authService;
	/**
	 * @var UserSession
	 */
	private $userSession = null;
	/**
	 * @var AbstractFactory
	 */
	private $factory;
	
	public function __construct(
		ContactRepositoryContract $contactRepository,
		ContactAddressRepositoryContract $addressRepository,
		OrderRepositoryContract $orderRepository,
		AuthenticationService $authService,
		AbstractFactory $factory)
	{
		$this->contactRepository = $contactRepository;
		$this->addressRepository = $addressRepository;
		$this->orderRepository   = $orderRepository;
		$this->authService       = $authService;
		$this->factory           = $factory;
	}
	
	public function getContactId():int
	{
		if($this->userSession === null)
		{
			$this->userSession = $this->factory->make(UserSession::class);
		}
		return $this->userSession->getCurrentContactId();
	}
	
	public function registerCustomer(array $contactData, $billingAddressData = null, $deliveryAddressData = null):Contact
	{
		$contact = $this->createContact($contactData);
		
		if($contact->id > 0)
		{
			//login
			$this->authService->loginWithContactId($contact->id, (string)$contactData['password']);
		}
		
		if($billingAddressData !== null)
		{
			$this->createAddress($billingAddressData, AddressType::BILLING);
			if($deliveryAddressData === null)
			{
				$this->createAddress($billingAddressData, AddressType::DELIVERY);
			}
		}
		
		if($deliveryAddressData !== null)
		{
			$this->createAddress($deliveryAddressData, AddressType::DELIVERY);
		}
		
		return $contact;
	}
	
	public function createContact(array $contactData):Contact
	{
		$contact = $this->contactRepository->createContact($contactData);
		return $contact;
	}
	
	public function getContact()
	{
		if($this->getContactId() > 0)
		{
			return $this->contactRepository->findContactById($this->getContactId());
		}
		return null;
	}
	
	public function updateContact(array $contactData)
	{
		if($this->getContactId() > 0)
		{
			return $this->contactRepository->updateContact($contactData, $this->getContactId());
		}
		
		return null;
	}
	
	public function getAddresses($type = null)
	{
		return $this->addressRepository->getAddresses($this->getContactId(), $type);
	}
	
	public function getAddress(int $addressId, int $type):Address
	{
		return $this->addressRepository->getAddress($addressId, $this->getContactId(), $type);
	}
	
	public function createAddress(array $addressData, int $type):Address
	{
		$response = $this->addressRepository->createAddress($addressData, $this->getContactId(), $type);
		
		if($type == AddressType::BILLING)
		{
			$this->addressRepository->createAddress($addressData, $this->getContactId(), AddressType::DELIVERY);
		}
		elseif($type == AddressType::DELIVERY)
		{
			$this->addressRepository->createAddress($addressData, $this->getContactId(), AddressType::BILLING);
		}
		
		return $response;
	}
	
	public function updateAddress(int $addressId, array $addressData, int $type):Address
	{
		return $this->addressRepository->updateAddress($addressData, $addressId, $this->getContactId(), $type);
	}
	
	public function deleteAddress(int $addressId, int $type)
	{
		$this->addressRepository->deleteAddress($addressId, $this->getContactId(), $type);
	}
	
	public function getOrders(int $page = 1, int $items = 50):array
	{
		return $this->orderRepository->allOrdersByContact(
			$this->getContactId(),
			$page,
			$items
		);
	}
	
	public function getLatestOrder():Order
	{
		return $this->orderRepository->getLatestOrderByContactId(
			$this->getContactId()
		);
	}
}
