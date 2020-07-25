<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use IO\Api\Resources\CustomerAddressResource;
use IO\Builder\Order\AddressType;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Tests\TestCase;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Repositories\AddressRepository;
use Plenty\Modules\Account\Contact\Contracts\ContactAccountRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Repositories\ContactAddressRepository;
use Plenty\Modules\Account\Contact\Repositories\ContactRepository;
use Plenty\Modules\Account\Models\Account;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\Repositories\WebstoreConfigurationRepository;
use Plenty\Modules\Webshop\Repositories\ContactRepository as WebshopContactRepository;
use Plenty\Plugin\Events\Dispatcher;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var CustomerService $customerService */
    protected $customerService;
    /** @var AddressRepository $addressRepositoryMock */
    protected $addressRepositoryMock;
    /** @var BasketService $basketServiceMock */
    protected $basketServiceMock;
    /** @var AccountService */
    protected $accountServiceMock;
    /** @var ContactAddressRepository $contactAddressRepositoryMock */
    protected $contactAddressRepositoryMock;
    /** @var ContactRepository $contactRepositoryMock */
    protected $contactRepositoryMock;
    /** @var WebshopContactRepository $webshopContactRepository */
    protected $webshopContactRepository;
    /** @var ContactAccountRepositoryContract $contactAccountRepositoryMock */
    protected $contactAccountRepositoryMock;
    /** @var SessionStorageRepositoryContract $sessionStorageRepositoryMock */
    protected $sessionStorageRepositoryMock;
    /** @var WebstoreConfigurationRepository $webstoreConfigurationRepositoryMock */
    protected $webstoreConfigurationRepositoryMock;
    /** @var Dispatcher $dispatcherMock */
    protected $dispatcherMock;


    protected function setUp(): void
    {
        parent::setUp();

        $this->addressRepositoryMock = Mockery::mock(AddressRepository::class);
        $this->replaceInstanceByMock(AddressRepository::class, $this->addressRepositoryMock);

        $this->basketServiceMock = Mockery::mock(BasketService::class);
        $this->replaceInstanceByMock(BasketService::class, $this->basketServiceMock);

        $this->accountServiceMock = Mockery::mock(AccountService::class);
        $this->replaceInstanceByMock(AccountService::class, $this->accountServiceMock);

        $this->contactAddressRepositoryMock = Mockery::mock(ContactAddressRepository::class);
        $this->replaceInstanceByMock(ContactAddressRepository::class, $this->contactAddressRepositoryMock);

        $this->contactRepositoryMock = Mockery::mock(ContactRepository::class);
        $this->replaceInstanceByMock(ContactRepository::class, $this->contactRepositoryMock);

        $this->webshopContactRepository = Mockery::mock(WebshopContactRepository::class);
        $this->replaceInstanceByMock(WebshopContactRepository::class, $this->webshopContactRepository);

        $this->contactAccountRepositoryMock = Mockery::mock(ContactAccountRepositoryContract::class);
        $this->replaceInstanceByMock(ContactAccountRepositoryContract::class, $this->contactAccountRepositoryMock);

        $this->sessionStorageRepositoryMock = Mockery::mock(SessionStorageRepositoryContract::class);
        $this->replaceInstanceByMock(SessionStorageRepositoryContract::class, $this->sessionStorageRepositoryMock);

        $this->webstoreConfigurationRepositoryMock = Mockery::mock(WebstoreConfigurationRepositoryContract::class);
        $this->replaceInstanceByMock(WebstoreConfigurationRepositoryContract::class, $this->webstoreConfigurationRepositoryMock);

        $this->dispatcherMock = Mockery::mock(Dispatcher::class);
        $this->replaceInstanceByMock(Dispatcher::class, $this->dispatcherMock);
        $this->dispatcherMock->makePartial();

        $this->customerService = pluginApp(CustomerService::class);
    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_billing_address_as_guest()
    {
        $addressId = 100;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                'id' => $addressId,
                'name1' => '',
                'gender' => 'male'  // avoid generating a custom company address
            ]
        );


        $addressArray = $address->toArray();

        $this->addressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $this->sessionStorageRepositoryMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageRepositoryContract::GUEST_EMAIL)
            ->andReturn('test@test.de')
            ->once();

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn(null);

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $response = $this->customerService->createAddress($addressArray, AddressType::BILLING);

        $this->assertInstanceOf(Address::class, $response);
        $this->assertEquals($response->id, $addressId);
    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_delivery_address_as_guest()
    {
        $addressId = 100;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                'id' => $addressId,
                'name1' => '',
                'gender' => 'male'
            ]
        );

        $addressArray = $address->toArray();

        $this->addressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $this->sessionStorageRepositoryMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageRepositoryContract::GUEST_EMAIL)
            ->andReturn('test@test.de')
            ->once();

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn(null);

        $this->basketServiceMock->shouldReceive('setDeliveryAddressId')->with($address->id)->andReturnNull()->once();

        $response = $this->customerService->createAddress($addressArray, AddressType::DELIVERY);

        $this->assertInstanceOf(Address::class, $response);
        $this->assertEquals($response->id, $addressId);
    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_billing_address_as_logged_in_user()
    {
        $addressId = 100;
        $contactId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                'id' => $addressId,
                'name1' => '',
                'gender' => 'male'
            ]
        );

        $addressArray = $address->toArray();

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $this->accountServiceMock->shouldReceive('getAccountContactId')->andReturn($contact->id);

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contact->id);

        $this->contactAddressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $response = $this->customerService->createAddress($addressArray, AddressType::BILLING);

        $this->assertInstanceOf(Address::class, $response);
        $this->assertEquals($response->id, $addressId);
    }

    /** @test */
    public function it_creates_a_billing_address_with_company_as_logged_in_user()
    {
        $addressId = 100;
        $contactId = 1;
        $accountId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                'id' => $addressId,
                'gender' => 'male'
            ]
        );

        $addressArray = $address->toArray();

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $account = factory(Account::class)->make(
            [
                "id" => $accountId,
            ]
        );

        $webstoreConfig = factory(WebstoreConfiguration::class)->make();

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contact->id);

        $this->contactAccountRepositoryMock->shouldReceive('createAccount')->andReturn($account)->once();

        $this->webstoreConfigurationRepositoryMock->shouldReceive('getWebstoreConfiguration')->andReturn($webstoreConfig);

        $this->contactAddressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $response = $this->customerService->createAddress($addressArray, AddressType::BILLING);

        // TODO: check if account and contact are created correctly

        $this->assertInstanceOf(Address::class, $response);
        $this->assertEquals($response->id, $addressId);
    }

    /** @test */
    public function it_sets_the_contacts_first_and_last_name_from_the_address_that_gets_created()
    {
        $addressId = 100;
        $contactId = 1;
        $accountId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                'id' => $addressId,
                'gender' => 'male'
            ]
        );

        $addressArray = $address->toArray();

        $contact = factory(Contact::class)->make(
            [
                'id' => $contactId,
                'firstName' => "",
                'lastName' => "",
            ]
        );


        $account = factory(Account::class)->make(
            [
                "id" => $accountId,
            ]
        );

        $webstoreConfig = factory(WebstoreConfiguration::class)->make();

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $this->accountServiceMock->shouldReceive('getAccountContactId')->andReturn($contact->id);

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contact->id);

        $this->contactRepositoryMock->shouldReceive('updateContact')->andReturn($contact)->once();

        $this->contactAccountRepositoryMock->shouldReceive('createAccount')->andReturn($account)->once();

        $this->webstoreConfigurationRepositoryMock->shouldReceive('getWebstoreConfiguration')->andReturn($webstoreConfig);

        $this->contactAddressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $this->sessionStorageRepositoryMock
            ->shouldReceive('getSessionValue')
            ->withArgs([SessionStorageRepositoryContract::GUEST_EMAIL])
            ->andReturn('test@test.com');

        $response = $this->customerService->createAddress($addressArray, AddressType::BILLING);

        $this->assertInstanceOf(Address::class, $response);
        $this->assertEquals($response->id, $addressId);
    }

    /** @test */
    public function it_deletes_an_existing_address_with_no_contact_id()
    {
        $addressId = 100;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
            ]
        );

        $this->addressRepositoryMock
            ->shouldReceive('deleteAddress')
            ->andReturn()
            ->once();

        $this->basketServiceMock
            ->shouldReceive(
                [
                    'getBillingAddressId' => $addressId,
                    'getDeliveryAddressId' => $addressId,
                ]
            );

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with(0)
            ->andReturn();

        $this->basketServiceMock
            ->shouldReceive('setDeliveryAddressId')
            ->with(CustomerAddressResource::ADDRESS_NOT_SET)
            ->andReturn();

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn(null);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn(0);

        try {
            $this->customerService->deleteAddress($address->id, AddressType::BILLING);
        } catch (\Exception $exception) {
            $this->fail('CustomerService failed! - ' . $exception->getMessage());
        }


        $this->assertTrue(true);
    }

    /** @test */
    public function it_deletes_an_existing_address_with_contact_and_billing_address_type()
    {
        $addressId = 100;

        $contactId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
            ]
        );

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $this->basketServiceMock
            ->shouldReceive(
                [
                    'getBillingAddressId' => $addressId,
                    'getDeliveryAddressId' => $addressId,
                ]
            );

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with(0)
            ->andReturn();

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contactId);


        $this->contactAddressRepositoryMock->shouldReceive('findContactAddressByTypeId')
            ->with($contactId, AddressType::BILLING, false)
            ->andReturn($address);

        $this->contactAddressRepositoryMock->shouldReceive('deleteAddress')
            ->once();


        $this->contactRepositoryMock->shouldReceive('updateContact')->andReturn($contact);

        try {
            $this->customerService->deleteAddress($address->id, AddressType::BILLING);
        } catch (\Exception $exception) {
            $this->fail('CustomerService failed! - ' . $exception->getMessage());
        }


        $this->assertTrue(true);
    }

    /** @test */
    public function it_deletes_an_existing_address_with_contact_and_delivery_address_type()
    {
        $addressId = 100;

        $contactId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
            ]
        );

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $this->basketServiceMock
            ->shouldReceive(
                [
                    'getBillingAddressId' => $addressId,
                    'getDeliveryAddressId' => $addressId,
                ]
            );

        $this->basketServiceMock
            ->shouldReceive('setDeliveryAddressId')
            ->with(CustomerAddressResource::ADDRESS_NOT_SET)
            ->andReturn();

        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contactId);

        $this->contactAddressRepositoryMock->shouldReceive('findContactAddressByTypeId')
            ->with($contactId, AddressType::DELIVERY, false)
            ->andReturn($address);

        $this->contactAddressRepositoryMock->shouldReceive('deleteAddress')
            ->once();


        $this->contactRepositoryMock->shouldReceive('updateContact')->andReturn($contact);

        try {
            $this->customerService->deleteAddress($address->id, AddressType::DELIVERY);
        } catch (\Exception $exception) {
            $this->fail('CustomerService failed! - ' . $exception->getMessage());
        }


        $this->assertTrue(true);
    }

    /** @test */
    public function it_updates_an_existing_address_as_guest_and_delivery_address_type()
    {
        $addressId = 100;

        $contactId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
                "gender" => "male"
            ]
        );

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn(null);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn(0);

        $address2 = $address->replicate();
        $address2->name1 = 'update';
        $address2->id = $addressId;

        $this->addressRepositoryMock->shouldReceive('findAddressById')
            ->with($addressId)
            ->andReturn($address);

        $this->addressRepositoryMock->shouldReceive('updateAddress')
            ->andReturn($address2);

        $this->basketServiceMock
            ->shouldReceive('setDeliveryAddressId')
            ->with($addressId)
            ->andReturn();

        $this->dispatcherMock->shouldReceive('fire')->andReturn();


        $this->sessionStorageRepositoryMock
            ->shouldReceive('getSessionValue')
            ->andReturnUsing(
                function ($args) {
                    if ($args == SessionStorageRepositoryContract::GUEST_EMAIL) {
                        return 'test@test.de';
                    }

                    return null;
                }
            );

        /** @var Address $updatedAddress */
        $updatedAddress = $this->customerService->updateAddress(
            $address->id,
            $address->toArray(),
            AddressType::DELIVERY
        );

        $this->assertNotNull($updatedAddress);
        $this->assertInstanceOf(Address::class, $updatedAddress);
        $this->assertEquals($addressId, $updatedAddress->id);
        $this->assertNotEquals($address->name1, $updatedAddress->name1);
    }

    /** @test */
    public function it_updates_an_existing_address_as_contact_and_delivery_address_type()
    {
        $addressId = 100;

        $contactId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
                "gender" => "male"
            ]
        );

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contactId);

        $address2 = $address->replicate();
        $address2->name1 = 'update';
        $address2->id = $addressId;

        $this->addressRepositoryMock->shouldReceive('findAddressById')
            ->with($addressId)
            ->andReturn($address);

        $this->contactAddressRepositoryMock->shouldReceive('updateAddress')
            ->andReturn($address2);

        $this->basketServiceMock
            ->shouldReceive('setDeliveryAddressId')
            ->with($addressId)
            ->andReturn();

        $this->dispatcherMock->shouldReceive('fire')->andReturn();

        /** @var Address $updatedAddress */
        $updatedAddress = $this->customerService->updateAddress(
            $address->id,
            $address->toArray(),
            AddressType::DELIVERY
        );

        $this->assertNotNull($updatedAddress);
        $this->assertInstanceOf(Address::class, $updatedAddress);
        $this->assertEquals($addressId, $updatedAddress->id);
        $this->assertNotEquals($address->name1, $updatedAddress->name1);
    }

    /** @test */
    public function it_updates_an_existing_address_as_contact_and_billing_address_type()
    {
        $addressId = 100;

        $contactId = 1;
        $accountId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
                "gender" => "male"
            ]
        );

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $account = factory(Account::class)->make(
            [
                "id" => $accountId,
            ]
        );

        $webstoreConfig = factory(WebstoreConfiguration::class)->make();

        $this->webstoreConfigurationRepositoryMock->shouldReceive('getWebstoreConfiguration')->andReturn($webstoreConfig);

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contactId);

        $this->contactAccountRepositoryMock->shouldReceive('createAccount')->andReturn($account)->once();

        $address2 = $address->replicate();
        $address2->name1 = 'update';
        $address2->id = $addressId;

        $this->addressRepositoryMock->shouldReceive('findAddressById')
            ->with($addressId)
            ->andReturn($address);

        $this->contactAddressRepositoryMock->shouldReceive('updateAddress')
            ->andReturn($address2);

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with($addressId)
            ->andReturn();

        $this->contactAddressRepositoryMock->shouldReceive('findContactAddressByTypeId')
            ->with($contactId, AddressType::BILLING, false)
            ->andReturn($address);

        $this->dispatcherMock->shouldReceive('fire')->andReturn();

        $this->contactRepositoryMock->shouldReceive('updateContact')->andReturn($contact);

        /** @var Address $updatedAddress */
        $updatedAddress = $this->customerService->updateAddress(
            $address->id,
            $address->toArray(),
            AddressType::BILLING
        );

        $this->assertNotNull($updatedAddress);
        $this->assertInstanceOf(Address::class, $updatedAddress);
        $this->assertEquals($addressId, $updatedAddress->id);
        $this->assertNotEquals($address->name1, $updatedAddress->name1);
    }

    /** @test */
    public function it_updates_an_existing_address_as_contact_and_billing_address_type_without_name1()
    {
        $addressId = 100;

        $contactId = 1;
        $accountId = 1;

        /** @var Address $address */
        $address = factory(Address::class)->make(
            [
                "id" => $addressId,
                "name1" => "",
                "gender" => "male"
            ]
        );

        $contact = factory(Contact::class)->make(
            [
                "id" => $contactId,
            ]
        );

        $account = factory(Account::class)->make(
            [
                "id" => $accountId,
            ]
        );

        $this->webshopContactRepository->shouldReceive('getContact')->andReturn($contact);
        $this->webshopContactRepository->shouldReceive('getContactId')->andReturn($contactId);

        $address2 = $address->replicate();
        $address2->name1 = 'update';
        $address2->id = $addressId;

        $this->addressRepositoryMock->shouldReceive('findAddressById')
            ->with($addressId)
            ->andReturn($address);

        $this->contactAddressRepositoryMock->shouldReceive('updateAddress')
            ->andReturn($address2);

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with($addressId)
            ->andReturn();

        $this->contactAddressRepositoryMock->shouldReceive('findContactAddressByTypeId')
            ->with($contactId, AddressType::BILLING, false)
            ->andReturn($address);

        $this->contactAddressRepositoryMock->shouldReceive('getAddress')
            ->with($addressId, $contactId, AddressType::BILLING)
            ->andReturn($address2);

        $this->dispatcherMock->shouldReceive('fire')->andReturn();

        $this->contactRepositoryMock->shouldReceive('updateContact')->andReturn($contact);

        /** @var Address $updatedAddress */
        $updatedAddress = $this->customerService->updateAddress(
            $address->id,
            $address->toArray(),
            AddressType::BILLING
        );

        $this->assertNotNull($updatedAddress);
        $this->assertInstanceOf(Address::class, $updatedAddress);
        $this->assertEquals($addressId, $updatedAddress->id);
        $this->assertNotEquals($address->name1, $updatedAddress->name1);
    }
}
