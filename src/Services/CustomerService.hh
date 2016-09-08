<?hh //strict

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
    private ContactRepositoryContract $contactRepository;
    private ContactAddressRepositoryContract $addressRepository;
    private OrderRepositoryContract $orderRepository;
    private AuthenticationService $authService;
    private ?UserSession $userSession = null;
    private AbstractFactory $factory;

    public function __construct(
        ContactRepositoryContract $contactRepository,
        ContactAddressRepositoryContract $addressRepository,
        OrderRepositoryContract $orderRepository,
        AuthenticationService $authService,
        AbstractFactory $factory )
    {
        $this->contactRepository = $contactRepository;
        $this->addressRepository = $addressRepository;
        $this->orderRepository = $orderRepository;
        $this->authService = $authService;
        $this->factory = $factory;
    }

    public function getContactId():int
    {
        if( $this->userSession === null )
        {
            $this->userSession = $this->factory->make( UserSession::class );
        }
        return $this->userSession->getCurrentContactId();
    }

    public function registerCustomer( array<string, mixed> $contactData, ?array<string, mixed> $billingAddressData = null, ?array<string, mixed> $deliveryAddressData = null ):Contact
    {
        $contact = $this->createContact( $contactData );

        if($contact->id > 0)
        {
            //login
            $this->authService->loginWithContactId($contact->id, (string)$contactData['password']);
        }

        if( $billingAddressData !== null )
        {
            $this->createAddress( $billingAddressData, AddressType::BILLING );
            if( $deliveryAddressData === null )
            {
                $this->createAddress( $billingAddressData, AddressType::DELIVERY );
            }
        }

        if( $deliveryAddressData !== null )
        {
            $this->createAddress( $deliveryAddressData, AddressType::DELIVERY );
        }

        return $contact;
    }

    public function createContact( array<string, mixed> $contactData ):Contact
    {
        $contact = $this->contactRepository->createContact( $contactData );
        return $contact;
    }

    public function getContact():?Contact
    {
        if( $this->getContactId() > 0 )
        {
            return $this->contactRepository->findContactById( $this->getContactId() );
        }
        return null;
    }

    public function updateContact( array<string, mixed> $contactData ):?Contact
    {
        if( $this->getContactId() > 0 )
        {
            return $this->contactRepository->updateContact( $contactData, $this->getContactId() );
        }

        return null;
    }

    public function getAddresses( ?AddressType $type = null ):array<Address>
    {
        return $this->addressRepository->getAddresses( $this->getContactId(), $type );
    }

    public function getAddress( int $addressId, AddressType $type ):Address
    {
        return $this->addressRepository->getAddress( $addressId, $this->getContactId(), $type );
    }

    public function createAddress( array<string, mixed> $addressData, AddressType $type ):Address
    {
        $response = $this->addressRepository->createAddress( $addressData, $this->getContactId(), $type );

        if($type == AddressType::BILLING)
        {
            $this->addressRepository->createAddress( $addressData, $this->getContactId(), AddressType::DELIVERY );
        }
        elseif($type == AddressType::DELIVERY)
        {
            $this->addressRepository->createAddress( $addressData, $this->getContactId(), AddressType::BILLING);
        }

        return $response;
    }

    public function updateAddress( int $addressId, array<string, mixed> $addressData, AddressType $type ):Address
    {
        return $this->addressRepository->updateAddress( $addressData, $addressId, $this->getContactId(), $type );
    }

    public function deleteAddress( int $addressId, AddressType $type ):void
    {
        $this->addressRepository->deleteAddress( $addressId, $this->getContactId(), $type );
    }

    public function getOrders( int $page = 1, int $items = 50 ):array<Order>
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
