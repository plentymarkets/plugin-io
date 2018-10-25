<?php

use IO\Helper\UserSession;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Tests\TestCase;
use IO\Validators\Customer\AddressValidator;
use Plenty\Modules\Account\Address\Models\Address;
use Mockery;
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

        $this->customerService = pluginApp(CustomerService::class);
    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_billing_address_as_guest()
    {
        $address = factory(Address::class)->create();
        $addressArray = $address->toArray();

        $this->addressValidatorMock->shouldReceive('validateOrFail')->andReturnNull()->once();
        $this->addressValidatorMock->shouldReceive('isEnAddress')->andReturn(false)->once();

        $this->addressRepositoryMock
            ->shouldReceive('createAddress')
            ->andReturn($address)
            ->once();

        $this->basketServiceMock->shouldReceive('setBillingAddressId')->with($address->id)->andReturnNull()->once();

        $this->customerService->createAddress($addressArray, 1);
    }

    /** @test
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function it_creates_an_billing_address_as_logged_in_user()
    {
        $address = factory(Address::class)->create();
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

        $this->customerService->createAddress($addressArray, 1);
    }
}