<?php

use IO\Api\Resources\CustomerAddressResource;
use IO\Constants\SessionStorageKeys;
use IO\Helper\UserSession;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use IO\Tests\TestCase;
use IO\Validators\Customer\AddressValidator;
use Plenty\Modules\Account\Address\Models\Address;
use IO\Builder\Order\AddressType;
use Plenty\Modules\Account\Address\Repositories\AddressRepository;
use Plenty\Modules\Account\Contact\Contracts\ContactAccountRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Repositories\ContactAddressRepository;
use Plenty\Modules\Account\Contact\Repositories\ContactRepository;
use Plenty\Modules\Account\Models\Account;
use Plenty\Modules\Account\Repositories\AccountRepository;

class CustomerServiceTest extends TestCase
{
    /** @var CustomerService $customerService */
    protected $customerService;
    /** @var AddressValidator $addressValidatorMock */
    protected $addressValidatorMock;
    /** @var AddressRepository $addressRepositoryMock */
    protected $addressRepositoryMock;
    /** @var BasketService $basketServiceMock */
    protected $basketServiceMock;
    /** @var UserSession $userSessionMock */
    protected $userSessionMock;
    /** @var ContactAddressRepository $contactAddressRepositoryMock */
    protected $contactAddressRepositoryMock;
    /** @var ContactRepository $contactRepositoryMock */
    protected $contactRepositoryMock;
    /** @var ContactAccountRepositoryContract $contactAccountRepositoryMock */
    protected $contactAccountRepositoryMock;
    /** @var SessionStorageService $sessionStorageServiceMock */
    protected $sessionStorageServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->addressValidatorMock = Mockery::mock(AddressValidator::class);
        app()->instance(AddressValidator::class, $this->addressValidatorMock);

        $this->addressRepositoryMock = Mockery::mock(AddressRepository::class);
        app()->instance(AddressRepository::class, $this->addressRepositoryMock);

        $this->basketServiceMock = Mockery::mock(BasketService::class);
        app()->instance(BasketService::class, $this->basketServiceMock);

        $this->userSessionMock = Mockery::mock(UserSession::class);
        app()->instance(UserSession::class, $this->userSessionMock);

        $this->contactAddressRepositoryMock = Mockery::mock(ContactAddressRepository::class);
        app()->instance(ContactAddressRepository::class, $this->contactAddressRepositoryMock);

        $this->contactRepositoryMock = Mockery::mock(ContactRepository::class);
        app()->instance(ContactRepository::class, $this->contactRepositoryMock);

        $this->contactAccountRepositoryMock = Mockery::mock(ContactAccountRepositoryContract::class);
        app()->instance(ContactAccountRepositoryContract::class, $this->contactAccountRepositoryMock);

        $this->sessionStorageServiceMock = Mockery::mock(SessionStorageService::class);
        app()->instance(SessionStorageService::class, $this->sessionStorageServiceMock);

        $this->customerService = pluginApp(CustomerService::class);
    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_billing_address_as_guest()
    {
        $addressId = 100;

        /** @var Address $address */
        $address = factory(Address::class)->make([
            'id' => $addressId,
            'name1' => null
        ]);

        $addressArray = $address->toArray();

        $this->addressValidatorMock->shouldReceive('validateOrFail')->andReturnNull()->once();
        $this->addressValidatorMock->shouldReceive('isEnAddress')->andReturn(false)->once();

        $this->addressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $this->sessionStorageServiceMock
            ->shouldReceive('getSessionValue')
            ->with(SessionStorageKeys::GUEST_EMAIL)
            ->andReturn('test@test.de')
            ->once();

        $this->userSessionMock->shouldReceive('getCurrentContactId')->andReturn(0);

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $this->customerService->createAddress($addressArray, AddressType::BILLING);

    }

    /** @test */
    public function it_creates_a_billing_address_with_company_as_guest()
    {

    }

    /** @test */
    public function it_creates_a_billing_address_with_company_as_logged_in_user()
    {

    }

    /** @test */
    public function it_sets_the_contacts_first_and_last_name_from_the_address_that_gets_created()
    {

    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_billing_address_as_logged_in_user()
    {
        $addressId = 100;

        /** @var Address $address */
        $address = factory(Address::class)->make([
            'id' => $addressId,
            'name1' => null
        ]);

        $addressArray = $address->toArray();

        $contact = factory(Contact::class)->create();

        $account = factory(Account::class)->create();

        $this->addressValidatorMock->shouldReceive('validateOrFail')->andReturnNull()->once();
        $this->addressValidatorMock->shouldReceive('isEnAddress')->andReturn(false)->once();

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $this->userSessionMock->shouldReceive('getCurrentContactId')->andReturn($contact->id);

        $this->contactRepositoryMock->shouldReceive('findContactById')->with($contact->id)->andReturn($contact);

        $this->contactAccountRepositoryMock->shouldReceive('createAccount')->andReturn($account)->once();

        $this->contactAddressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $this->customerService->createAddress($addressArray, AddressType::BILLING);
    }

    /** @test */
    public function it_deletes_an_existing_address_with_no_contact_id()
    {
        $addressId = 100;

        /** @var Address $address */
        $address = factory(Address::class)->make([
            "id" => $addressId
        ]);

        $this->addressRepositoryMock
            ->shouldReceive('deleteAddress')
            ->andReturn()
            ->once();

        $this->basketServiceMock
            ->shouldReceive(
                [
                    'getBillingAddressId' => $addressId,
                    'getDeliveryAddressId' => $addressId
                ]);

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with(0)
            ->andReturn();

        $this->basketServiceMock
            ->shouldReceive('setDeliveryAddressId')
            ->with(CustomerAddressResource::ADDRESS_NOT_SET)
            ->andReturn();

        $this->userSessionMock->shouldReceive('getCurrentContactId')->andReturn(0);

        try {

            $this->customerService->deleteAddress($address->id, AddressType::BILLING);

        }catch (\Exception $exception) {

            $this->fail('CustomerService failed! - '. $exception->getMessage());
        }


        $this->assertTrue(true);

    }
}