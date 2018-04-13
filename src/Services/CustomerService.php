<?php //strict

namespace IO\Services;

use IO\Api\Resources\CustomerAddressResource;
use IO\Builder\Order\OrderType;
use IO\Helper\MemoryCache;
use IO\Models\LocalizedOrder;
use IO\Validators\Customer\ContactValidator;
use IO\Validators\Customer\AddressValidator;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Account\Contact\Contracts\ContactAccountRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use IO\Builder\Order\AddressType;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Contact\Models\ContactOption;
use Plenty\Modules\Account\Models\Account;
use Plenty\Modules\Authorization\Services\AuthHelper;
use IO\Helper\UserSession;
use Plenty\Modules\Frontend\Events\FrontendCustomerAddressChanged;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use IO\Services\SessionStorageService;
use IO\Constants\SessionStorageKeys;
use IO\Services\OrderService;
use IO\Services\NotificationService;
use IO\Services\CustomerPasswordResetService;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Account\Contact\Contracts\ContactClassRepositoryContract;


/**
 * Class CustomerService
 * @package IO\Services
 */
class CustomerService
{
    use MemoryCache;

    /**
     * @var ContactAccountRepositoryContract $accountRepository
     */
    private $accountRepository;
    
	/**
	 * @var ContactRepositoryContract
	 */
	private $contactRepository;
	/**
	 * @var ContactAddressRepositoryContract
	 */
	private $contactAddressRepository;
    /**
     * @var AddressRepositoryContract
     */
    private $addressRepository;
    /**
     * @var SessionStorageService
     */
    private $sessionStorage;
	/**
	 * @var UserSession
	 */
	private $userSession = null;
    
    /**
     * CustomerService constructor.
     * @param ContactAccountRepositoryContract $accountRepository
     * @param ContactRepositoryContract $contactRepository
     * @param ContactAddressRepositoryContract $contactAddressRepository
     * @param AddressRepositoryContract $addressRepository
     * @param \IO\Services\SessionStorageService $sessionStorage
     */
	public function __construct(
        ContactAccountRepositoryContract $accountRepository,
		ContactRepositoryContract $contactRepository,
		ContactAddressRepositoryContract $contactAddressRepository,
        AddressRepositoryContract $addressRepository,
        SessionStorageService $sessionStorage)
	{
	    $this->accountRepository        = $accountRepository;
		$this->contactRepository        = $contactRepository;
		$this->contactAddressRepository = $contactAddressRepository;
        $this->addressRepository        = $addressRepository;
        $this->sessionStorage           = $sessionStorage;
	}

    /**
     * Get the ID of the current contact from the session
     * @return int
     */
	public function getContactId():int
	{
		if($this->userSession === null)
		{
			$this->userSession = pluginApp(UserSession::class);
		}
		return $this->userSession->getCurrentContactId();
	}
	
	public function getContactClassData($contactClassId)
    {
        return $this->fromMemoryCache(
            "contactClassData.$contactClassId",
            function() use ($contactClassId)
            {
                /** @var ContactClassRepositoryContract $contactClassRepo */
                $contactClassRepo = pluginApp(ContactClassRepositoryContract::class);

                /** @var AuthHelper $authHelper */
                $authHelper = pluginApp(AuthHelper::class);

                $contactClass = $authHelper->processUnguarded( function() use ($contactClassRepo, $contactClassId)
                {
                    return $contactClassRepo->findContactClassDataById($contactClassId);
                });

                return $contactClass;
            }
        );
    }

    public function showNetPrices()
    {
        return $this->fromMemoryCache(
            "showNetPrices",
            function()
            {
                $customerShowNet = false;
                /** @var SessionStorageService $sessionStorageService */
                $sessionStorageService = pluginApp( SessionStorageService::class );
                $customer = $sessionStorageService->getCustomer();
                if ( $customer !== null )
                {
                    $customerShowNet = $customer->showNetPrice;
                }

                $contactClassShowNet = false;
                $contactClassId = $this->getContactClassId();
                if ( $contactClassId !== null )
                {
                    $contactClass = $this->getContactClassData( $contactClassId );
                    if ( $contactClass !== null )
                    {
                        $contactClassShowNet = $contactClass['showNetPrice'];
                    }
                }

                return $customerShowNet || $contactClassShowNet;
            }
        );
    }
    
    public function getContactClassMinimumOrderQuantity()
    {
        $contact = $this->getContact();
    
        if($contact instanceof Contact)
        {
            $contactClassId = $contact->classId;
        
            $contactClass = $this->getContactClassData($contactClassId);
        
            if( is_array($contactClass) && count($contactClass) && isset($contactClass['minItemQuantity']))
            {
                return (int)$contactClass['minItemQuantity'];
            }
        
            return 0;
        }
    
        return 0;
    }

    /**
     * Create a contact with addresses if specified
     * @param array $contactData
     * @param null $billingAddressData
     * @param null $deliveryAddressData
     * @return Contact
     */
	public function registerCustomer(array $contactData, $billingAddressData = null, $deliveryAddressData = null)
	{
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        
        $newBillingAddress = null;
        $newDeliveryAddress = null;
        
        $guestBillingAddress = null;
        //$guestBillingAddressId = $this->sessionStorage->getSessionValue(SessionStorageKeys::BILLING_ADDRESS_ID);
        $guestBillingAddressId = $basketService->getBillingAddressId();
        if((int)$guestBillingAddressId > 0)
        {
            $guestBillingAddress = $this->addressRepository->findAddressById($guestBillingAddressId);
        }
        
        $guestDeliveryAddress = null;
        //$guestDeliveryAddressId = $this->sessionStorage->getSessionValue(SessionStorageKeys::DELIVERY_ADDRESS_ID);
        $guestDeliveryAddressId = $basketService->getDeliveryAddressId();
        if((int)$guestDeliveryAddressId > 0)
        {
            $guestDeliveryAddress = $this->addressRepository->findAddressById($guestDeliveryAddressId);
        }
        
        $contact = $this->createContact($contactData);
        
        if(!is_null($contact) && $contact->id > 0)
        {
            //Login
            pluginApp(AuthenticationService::class)->loginWithContactId($contact->id, (string)$contactData['password']);
            
            if($guestBillingAddress !== null)
            {
                $newBillingAddress = $this->createAddress(
                    $guestBillingAddress->toArray(),
                    AddressType::BILLING
                );
                //$this->sessionStorage->setSessionValue(SessionStorageKeys::BILLING_ADDRESS_ID, $newBillingAddress->id);
                $basketService->setBillingAddressId($newBillingAddress->id);
            }
            
            if($guestDeliveryAddress !== null)
            {
                $newDeliveryAddress = $this->createAddress(
                    $guestDeliveryAddress->toArray(),
                    AddressType::DELIVERY
                );
                //$this->sessionStorage->setSessionValue(SessionStorageKeys::DELIVERY_ADDRESS_ID, $newDeliveryAddress->id);
                $basketService->setDeliveryAddressId($newDeliveryAddress->id);
            }
    
            if($billingAddressData !== null)
            {
                $newBillingAddress = $this->createAddress($billingAddressData, AddressType::BILLING);
                //$this->sessionStorage->setSessionValue(SessionStorageKeys::BILLING_ADDRESS_ID, $newBillingAddress->id);
                $basketService->setBillingAddressId($newBillingAddress->id);
            }
    
            if($deliveryAddressData !== null)
            {
                $newDeliveryAddress = $this->createAddress($deliveryAddressData, AddressType::DELIVERY);
                //$this->sessionStorage->setSessionValue(SessionStorageKeys::DELIVERY_ADDRESS_ID, $newDeliveryAddress->id);
                $basketService->setDeliveryAddressId($newDeliveryAddress->id);
            }
    
            if($newBillingAddress instanceof Address)
            {
                $contact = $this->updateContactWithAddressData($newBillingAddress);
            }
        }
        
		return $contact;
	}

	public function createAccount($accountData)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $contactId = $this->getContactId();
        $accountRepo = $this->accountRepository;
        
        $account = $authHelper->processUnguarded( function() use ($accountData, $contactId, $accountRepo)
        {
            return $accountRepo->createAccount($accountData, (int)$contactId);
        });
        
        if($account instanceof Account && (int)$account->id > 0)
        {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $classId = (int)$templateConfigService->get('global.default_contact_class_b2b');
            
            if(is_null($classId) || (int)$classId <= 0)
            {
                $classId = $this->getDefaultContactClassId();
            }
    
            if(!is_null($classId) && (int)$classId > 0)
            {
                $this->updateContact([
                                         'classId' => $classId
                                     ]);
            }
        }
    }
    
    private function mapAddressDataToAccount($addressData)
    {
        return [
            'companyName' => $addressData['name1'],
            'taxIdNumber' => (isset($addressData['vatNumber']) && !is_null($addressData['vatNumber']) ? $addressData['vatNumber'] : ''),
        ];
    }
	
    /**
     * Create a new contact
     * @param array $contactData
     * @return Contact
     */
	public function createContact(array $contactData)
	{
	    $contact = null;
	    $contactData['checkForExistingEmail'] = true;
	    
	    try
        {
            $contact = $this->contactRepository->createContact($contactData);
        }
        catch(\Exception $e)
        {
            $contact = [
                            'code'      => 1,
                            'message'   => 'email already exists'
                       ];
        }
		
		return $contact;
	}

    /**
     * Find the current contact by ID
     * @return null|Contact
     */
	public function getContact()
	{
	    $contactId = $this->getContactId();
		if($contactId > 0)
		{
			return $this->fromMemoryCache(
			    "contact.$contactId",
                function() use ($contactId)
                {
                    return $this->contactRepository->findContactById($this->getContactId());
                }
            );
		}
		return null;
	}

	public function getContactClassId()
    {
        $contact = $this->getContact();
        if ( $contact !== null && $contact->classId !== null )
        {
            return $contact->classId;
        }
        else
        {
            return $this->getDefaultContactClassId();
        }
    }
    
    private function getDefaultContactClassId()
    {
        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        return $webstoreConfigService->getWebstoreConfig()->defaultCustomerClassId;
    }

    /**
     * Update a contact
     * @param array $contactData
     * @return null|Contact
     */
	public function updateContact(array $contactData)
	{
		if($this->getContactId() > 0)
		{
			return $this->contactRepository->updateContact($contactData, $this->getContactId());
		}

		return null;
	}

    /**
     * @param Address $address
     * @return null|Contact
     */
	private function updateContactWithAddressData(Address $address)
    {
        $contactData = [];
        $contact = null;
        
        $contactData['gender'] = $address->gender;
        $contactData['firstName'] = $address->firstName;
        $contactData['lastName'] = $address->lastName;
        $contactData['birthdayAt'] = $address->birthday;
        $contactData['options'] = $this->getContactOptionsFromAddress($address->options);

        $contact = $this->updateContact($contactData);

        return $contact;
    }

    /**
     * @param $addressOptions
     * @return array
     */
    private function getContactOptionsFromAddress($addressOptions)
    {
        $options = [];
        $addressToContactOptionsMap =
        [
            AddressOption::TYPE_TELEPHONE =>
            [
                'typeId' => ContactOption::TYPE_PHONE,
                'subTypeId' => ContactOption::SUBTYPE_PRIVATE
            ]
        ];

        foreach($addressOptions as $key => $addressOption)
        {
            $mapItem = $addressToContactOptionsMap[$addressOption->typeId];

            if(!empty($mapItem))
            {
                $options[] =
                [
                    'typeId' => $mapItem['typeId'],
                    'subTypeId' => $mapItem['subTypeId'],
                    'priority' => 0,
                    'value' => $addressOption->value
                ];
            }
        }

        return $options;
    }
	
	public function updatePassword($newPassword, $contactId = 0, $hash='')
    {
        /**
         * @var CustomerPasswordResetService $customerPasswordResetService
         */
        $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);
        
        if((int)$this->getContactId() <= 0 && strlen($hash) && $customerPasswordResetService->checkHash($contactId, $hash))
        {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $contactRepo = $this->contactRepository;
            
            $result = $authHelper->processUnguarded( function() use ($newPassword, $contactId, $contactRepo)
            {
                return $contactRepo->updateContact([
                                                        'changeOnlyPassword' => true,
                                                        'password'           => $newPassword
                                                   ],
                                                   (int)$contactId);
            });
            
            if($result instanceof Contact && (int)$result->id > 0)
            {
                $customerPasswordResetService->deleteHash($contactId);
            }
        }
        else
        {
            $result = $this->updateContact([
                                                'changeOnlyPassword' => true,
                                                'password'           => $newPassword
                                           ]);
        }
        
        return $result;
    }

    /**
     * List the addresses of a contact
     * @param null $type
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
	public function getAddresses($type = null)
	{
        if($this->getContactId() > 0)
        {
            return $this->contactAddressRepository->getAddresses($this->getContactId(), $type);
        }
        else
        {
            /**
             * @var BasketService $basketService
             */
            $basketService = pluginApp(BasketService::class);
            
            $address = null;
            
            if($type == AddressType::BILLING && $basketService->getBillingAddressId() > 0)
            {
                $address = $this->addressRepository->findAddressById($basketService->getBillingAddressId());
            }
            elseif($type == AddressType::DELIVERY && $basketService->getDeliveryAddressId() > 0)
            {
                $address = $this->addressRepository->findAddressById($basketService->getDeliveryAddressId());
            }
    
            if($address instanceof Address)
            {
                return [
                    $address
                ];
            }
    
            return [];
        }
	}

    /**
     * Get an address by ID
     * @param int $addressId
     * @param int $type
     * @return Address
     */
	public function getAddress(int $addressId, int $type):Address
	{
        if($this->getContactId() > 0)
        {
            return $this->contactAddressRepository->getAddress($addressId, $this->getContactId(), $type);
        }
        else
        {
            /**
             * @var BasketService $basketService
             */
            $basketService = pluginApp(BasketService::class);
            
            if($type == AddressType::BILLING)
            {
                return $this->addressRepository->findAddressById($basketService->getBillingAddressId());
            }
            elseif($type == AddressType::DELIVERY)
            {
                return $this->addressRepository->findAddressById($basketService->getDeliveryAddressId());
            }
        }
	}

    /**
     * Create an address with the specified address type
     * @param array $addressData
     * @param int $type
     * @return Address
     */
	public function createAddress(array $addressData, int $type):Address
	{
        AddressValidator::validateOrFail($type, $addressData);
        
        if(AddressValidator::isEnAddress($addressData['countryId']))
        {
            $addressData['useAddressLightValidator'] = true;
        }
        
        if (isset($addressData['stateId']) && empty($addressData['stateId']))
        {
            $addressData['stateId'] = null;
        }

        if (isset($addressData['gender']) && empty($addressData['gender']))
        {
            $addressData['gender'] = null;
        }
        
        $newAddress = null;
        
        if($this->getContactId() > 0)
        {
            $addressData['options'] = $this->buildAddressEmailOptions([], false, $addressData);
            $newAddress = $this->contactAddressRepository->createAddress($addressData, $this->getContactId(), $type);
            
            if($type == AddressType::BILLING && isset($addressData['name1']) && strlen($addressData['name1']))
            {
                $this->createAccount($this->mapAddressDataToAccount($addressData));
            }
            
            $existingContact = $this->getContact();
            if($type == AddressType::BILLING && !strlen($existingContact->firstName) && !strlen($existingContact->lastName))
            {
                $this->updateContactWithAddressData($newAddress);
            }
        }
		else
        {
            $addressData['options'] = $this->buildAddressEmailOptions([], true, $addressData);
            $newAddress =  $this->addressRepository->createAddress($addressData);
        }
        
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        
        if($newAddress instanceof Address)
        {
            if($type == AddressType::BILLING)
            {
                $basketService->setBillingAddressId($newAddress->id);
            }
            elseif($type == AddressType::DELIVERY)
            {
                $basketService->setDeliveryAddressId($newAddress->id);
            }
        }
        
        return $newAddress;
	}
	
	private function buildAddressEmailOptions(array $options = [], $isGuest = false, $addressData = [])
    {
        if($isGuest)
        {
            /**
             * @var SessionStorageService $sessionStorage
             */
            $sessionStorage = pluginApp(SessionStorageService::class);
            $email = $sessionStorage->getSessionValue(SessionStorageKeys::GUEST_EMAIL);
        }
        else
        {
            $email = $this->getContact()->email;
        }
        
        if(strlen($email))
        {
            $options[] = [
                'typeId' => AddressOption::TYPE_EMAIL,
                'value' => $email
            ];
        }
        
        if(count($addressData))
        {
            if(isset($addressData['vatNumber']))
            {
                $options[] = [
                    'typeId' => AddressOption::TYPE_VAT_NUMBER,
                    'value'  => $addressData['vatNumber']
                ];
            }
            
            if(isset($addressData['birthday']))
            {
                $options[] = [
                    'typeId' => AddressOption::TYPE_BIRTHDAY,
                    'value'  => $addressData['birthday']
                ];
            }

            if(isset($addressData['title']))
            {
                $options[] = [
                    'typeId' => AddressOption::TYPE_TITLE,
                    'value'  => $addressData['title']
                ];
            }

            if(isset($addressData['telephone']))
            {
                $options[] = [
                    'typeId' => AddressOption::TYPE_TELEPHONE,
                    'value'  => $addressData['telephone']
                ];
            }
            
            if(isset($addressData['address2']) && (strtoupper($addressData['address1']) == 'PACKSTATION' || strtoupper($addressData['address1']) == 'POSTFILIALE') && isset($addressData['address3']))
            {
                $options[] =
                [
                    'typeId' => 6,
                    'value' => $addressData['address3']
                ];
            }
            
        }
        
        return $options;
    }

    /**
     * Update an address
     * @param int $addressId
     * @param array $addressData
     * @param int $type
     * @return Address
     */
    public function updateAddress(int $addressId, array $addressData, int $type):Address
    {
        AddressValidator::validateOrFail($type, $addressData);

        if (isset($addressData['stateId']) && empty($addressData['stateId']))
        {
            $addressData['stateId'] = null;
        }

        if (isset($addressData['checkedAt']) && empty($addressData['checkedAt']))
        {
            unset($addressData['checkedAt']);
        }

        if (isset($addressData['gender']) && empty($addressData['gender']))
        {
            $addressData['gender'] = null;
        }

        if((int)$this->getContactId() > 0)
        {
            $addressData['options'] = $this->buildAddressEmailOptions([], false, $addressData);
    
            if($type == AddressType::BILLING && isset($addressData['name1']) && strlen($addressData['name1']))
            {
                $this->createAccount($this->mapAddressDataToAccount($addressData));
            }
            elseif($type == AddressType::BILLING && (!isset($addressData['name1']) || !strlen($addressData['name1'])))
            {
                $existingAddress = $this->getAddress($addressId, AddressType::BILLING);
                if($existingAddress instanceof Address && strlen($existingAddress->name1))
                {
                    $addressData['name1'] = $existingAddress->name1;
                }
            }
            
            $newAddress = $this->contactAddressRepository->updateAddress($addressData, $addressId, $this->getContactId(), $type);

            if($type == AddressType::BILLING) {

                $firstStoredAddress = $this->contactAddressRepository->findContactAddressByTypeId((int)$this->getContactId(),$type, false);

                if($addressId == $firstStoredAddress->id) {
                    $this->updateContactWithAddressData($newAddress);
                }
            }



        }
        else
        {
            //case for guests
            $addressData['options'] = $this->buildAddressEmailOptions([], true, $addressData);
            $newAddress = $this->addressRepository->updateAddress($addressData, $addressId);
        }
    
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
    
        $authHelper->processUnguarded( function() use ($type, $newAddress)
        {
            /**
             * @var BasketService $basketService
             */
            $basketService = pluginApp(BasketService::class);
            if($type == AddressType::BILLING)
            {
                $basketService->setBillingAddressId($newAddress->id);
            }
            elseif($type == AddressType::DELIVERY)
            {
                $basketService->setDeliveryAddressId($newAddress->id);
            }
        });

        //fire public event
        /** @var Dispatcher $pluginEventDispatcher */
        $pluginEventDispatcher = pluginApp(Dispatcher::class);
        $pluginEventDispatcher->fire(FrontendCustomerAddressChanged::class);

        return $newAddress;
    }

    /**
     * Delete an address
     * @param int $addressId
     * @param int $type
     */
	public function deleteAddress(int $addressId, int $type = 0)
	{
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
	    
        if($this->getContactId() > 0)
        {
            $firstStoredAddress = $this->contactAddressRepository->findContactAddressByTypeId((int)$this->getContactId(),$type, false);

            $this->contactAddressRepository->deleteAddress($addressId, $this->getContactId(), $type);
            
            if($type == AddressType::BILLING)
            {
                $basketService->setBillingAddressId(0);
            }
            elseif($type == AddressType::DELIVERY)
            {
                $basketService->setDeliveryAddressId(CustomerAddressResource::ADDRESS_NOT_SET);
            }

            if($firstStoredAddress instanceof Address && $firstStoredAddress->id === $addressId)
            {
                $firstStoredAddress = $this->contactAddressRepository->findContactAddressByTypeId((int)$this->getContactId(),$type, false);

                if($firstStoredAddress instanceof Address)
                {
                    $this->updateContactWithAddressData($firstStoredAddress);
                }
            }
        }
        else
        {
            $this->addressRepository->deleteAddress($addressId);
            if($addressId == $basketService->getBillingAddressId())
            {
                $basketService->setBillingAddressId(0);
            }
            elseif($addressId == $basketService->getDeliveryAddressId())
            {
                $basketService->setDeliveryAddressId(CustomerAddressResource::ADDRESS_NOT_SET);
            }
        }
	}

    /**
     * Get a list of orders for the current contact
     * @param int $page
     * @param int $items
     * @param array $filters
     * @return array|\Plenty\Repositories\Models\PaginatedResult
     */
	public function getOrders(int $page = 1, int $items = 10, array $filters = [])
	{
		$orders = [];
        
        try
        {
            $orders = pluginApp(OrderService::class)->getOrdersForContact(
                $this->getContactId(),
                $page,
                $items,
                $filters
            );
        }
        catch(\Exception $e)
        {}

        return $orders;
	}
	
	public function hasReturns()
    {
        $returns = $this->getReturns(1, 1, [], false);
        if(count($returns->getResult()))
        {
            return true;
        }
        
        return false;
    }
	
	public function getReturns(int $page = 1, int $items = 10, array $filters = [], $wrapped = true)
    {
        $filters['orderType'] = OrderType::RETURNS;
        
        return pluginApp(OrderService::class)->getOrdersForContact(
            $this->getContactId(),
            $page,
            $items,
            $filters,
            $wrapped
        );
    }

    /**
     * Get the last order created by the current contact
     * @return LocalizedOrder
     */
	public function getLatestOrder()
	{
        return pluginApp(OrderService::class)->getLatestOrderForContact(
            $this->getContactId()
        );
	}
	
	public function resetGuestAddresses()
    {
        if($this->getContactId() <= 0)
        {
            /**
             * @var BasketService $basketService
             */
            $basketService = pluginApp(BasketService::class);
            
            $basketService->setBillingAddressId(0);
            $basketService->setDeliveryAddressId(0);
            
            $this->sessionStorage->setSessionValue(SessionStorageKeys::GUEST_EMAIL, null);
        }
    }
}
