<?php

namespace IO\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IO\Builder\Order\AddressType;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Tests\TestCase;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Services\CheckoutService;
use \Mockery;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Events\Dispatcher;

class CustomerServiceFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @var CustomerService $customerService */
    protected $customerService;

    /** @var CheckoutService $checkoutService */
    protected $checkoutService;

    /** @var Dispatcher $dispatcher */
    protected $dispatcher;

    protected $genders = ['male', 'female', 'diverse'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerService = pluginApp(CustomerService::class);
        $this->checkoutService = Mockery::mock(CheckoutService::class);
        app()->instance(CheckoutService::class, $this->checkoutService);

        $this->dispatcher = Mockery::mock(Dispatcher::class)->makePartial();
        app()->instance(Dispatcher::class, $this->dispatcher);
    }

    /**
     * @test
     * @dataProvider createAddressProvider
     * @param $addressData
     * @param $addressType
     */
    public function should_add_a_new_address_as_guest($addressData, $addressType)
    {
        $this->createAddress($addressData, $addressType);
    }

    /**
     * @test
     * @dataProvider createAddressProvider
     * @param $addressData
     * @param $addressType
     */
    public function should_add_a_new_address_as_logged_in_user($addressData, $addressType)
    {
        $email = $this->fake->email;
        $password = $this->fake->password;

        $this->checkoutService
            ->shouldReceive('getCustomerShippingAddressId')
            ->andReturn($addressData['countryId']);

        $this->checkoutService
            ->shouldReceive('getCustomerInvoiceAddressId')
            ->andReturn($addressData['countryId']);

        $this->createContact($email, $password);
        $this->performLogin($email, $password);
        $this->createAddress($addressData, $addressType);
    }


    private function createAddress($addressData, $addressType)
    {
        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL, $this->fake->email);

        if ($addressType === AddressType::BILLING) {
            $this->checkoutService
                ->shouldReceive('setCustomerInvoiceAddressId')
                ->andReturn(null);
        } elseif ($addressType === AddressType::DELIVERY) {
            $this->checkoutService
                ->shouldReceive('setCustomerShippingAddressId')
                ->andReturn(null);
        }

        $newAddress = $this->customerService->createAddress($addressData, $addressType);

        $this->assertNotNull($newAddress);
        $this->assertInstanceOf(Address::class, $newAddress);

        $this->assertAddressFieldsAreEqual($addressData, $newAddress);

        if ($addressData['address1'] == 'POSTFILIALE') {
            $this->assertTrue($newAddress->isPostfiliale);
        } elseif ($addressData['address1'] == 'PACKSTATION') {
            $this->assertTrue($newAddress->isPackstation);
        }
    }

    /**
     * @test
     * @dataProvider deleteAddressProvider
     * @param $addressData
     * @param $addressType
     */
    public function should_delete_an_address_as_guest($addressData, $addressType)
    {
        $this->deleteAddress($addressData, $addressType);
    }

    /**
     * @test
     * @dataProvider deleteAddressProvider
     * @param $addressData
     * @param $addressType
     */
    public function should_delete_an_address_as_logged_in_user($addressData, $addressType)
    {
        $email = $this->fake->email;
        $password = $this->fake->password;

        $this->checkoutService
            ->shouldReceive('getCustomerShippingAddressId')
            ->andReturn(null);

        $this->checkoutService
            ->shouldReceive('getCustomerInvoiceAddressId')
            ->andReturn(null);

        $this->checkoutService
            ->shouldReceive('getShippingCountryId')
            ->andReturn(null);

        $this->createContact($email, $password);
        $this->performLogin($email, $password);
        $this->deleteAddress($addressData, $addressType);
    }

    private function deleteAddress($addressData, $addressType)
    {
        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL, $this->fake->email);

        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);

        $this->checkoutService
            ->shouldReceive('getCustomerInvoiceAddressId')
            ->andReturn(null);

        $this->checkoutService
            ->shouldReceive('getCustomerShippingAddressId')
            ->andReturn(null);


        if ($addressType === AddressType::BILLING) {
            $this->checkoutService
                ->shouldReceive('setCustomerInvoiceAddressId')
                ->andReturn(null);
        } elseif ($addressType === AddressType::DELIVERY) {
            $this->checkoutService
                ->shouldReceive('setCustomerShippingAddressId')
                ->andReturn(null);
        }

        $address = $this->customerService->createAddress($addressData, $addressType);
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $authHelper->processUnguarded(function () use ($address, $addressType) {
            $this->customerService->deleteAddress($address->id, $addressType);
        });

        if ($addressType == AddressType::BILLING) {
            $this->assertEquals($basketService->getBillingAddressId(), 0);
        } elseif ($addressType == AddressType::DELIVERY) {
            $this->assertNull($basketService->getDeliveryAddressId());
        }
    }

    /**
     * @test
     * @dataProvider updateAddressProvider
     * @param $addressDataCreate
     * @param $addressDataUpdate
     * @param $addressType
     */
    public function should_update_an_address_as_guest($addressDataCreate, $addressDataUpdate, $addressType)
    {
        $this->updateAddress($addressDataCreate, $addressDataUpdate, $addressType);
    }

    /**
     * @test
     * @dataProvider updateAddressProvider
     * @param $addressDataCreate
     * @param $addressDataUpdate
     * @param $addressType
     */
    public function should_update_an_address_as_logged_in_user($addressDataCreate, $addressDataUpdate, $addressType)
    {
        $email = $this->fake->email;
        $password = $this->fake->password;

        $this->checkoutService
            ->shouldReceive('getCustomerShippingAddressId')
            ->andReturn($addressDataCreate['countryId']);


        $this->checkoutService
            ->shouldReceive('getCustomerInvoiceAddressId')
            ->andReturn($addressDataCreate['countryId']);

        $this->checkoutService
            ->shouldReceive('getShippingCountryId')
            ->andReturn(null);

        $this->createContact($email, $password);
        $this->performLogin($email, $password);
        $this->updateAddress($addressDataCreate, $addressDataUpdate, $addressType);
    }

    private function updateAddress($addressDataCreate, $addressDataUpdate, $addressType)
    {
        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL, $this->fake->email);

        if ($addressType === AddressType::BILLING) {
            $this->checkoutService
                ->shouldReceive('setCustomerInvoiceAddressId')
                ->andReturn(null);
        } elseif ($addressType === AddressType::DELIVERY) {
            $this->checkoutService
                ->shouldReceive('setCustomerShippingAddressId')
                ->andReturn(null);
        }

        $this->dispatcher->shouldReceive('fire');

        $address = $this->customerService->createAddress($addressDataCreate, $addressType);

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $updatedAddress = null;
        $authHelper->processUnguarded(function () use(&$updatedAddress, $address, $addressDataUpdate, $addressType) {
                $updatedAddress = $this->customerService->updateAddress($address->id, $addressDataUpdate, $addressType);
        });

        $this->assertNotNull($updatedAddress);
        $this->assertInstanceOf(Address::class, $updatedAddress);
        $this->assertEquals($address->id, $updatedAddress->id);
        $this->assertAddressFieldsAreEqual($addressDataUpdate, $updatedAddress->toArray());

        if ($addressDataUpdate['address1'] == 'POSTFILIALE') {
            $this->assertTrue($updatedAddress->isPostfiliale);
        } elseif ($addressDataUpdate['address1'] == 'PACKSTATION') {
            $this->assertTrue($updatedAddress->isPackstation);
        }
    }

    public function createAddressProvider()
    {
        return [
            [
                [
                    // Billing address with company and empty gender and stateId
                    'gender' => '',
                    'name1' => $this->fake->company,
                    'name2' => '',
                    'name3' => '',
                    'name4' => '',
                    'address1' => $this->fake->streetName,
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                    'stateId' => '',
                    'contactPerson' => $this->fake->name
                ],
                AddressType::BILLING,
            ],

            [
                [
                    // Billing address
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => '',
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => $this->fake->streetName,
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                    'stateId' => '',
                ],
                AddressType::BILLING,
            ],

            [
                [
                    // Delivery address with company
                    'gender' => '',
                    'name1' => $this->fake->company,
                    'name2' => '',
                    'name3' => '',
                    'name4' => '',
                    'address1' => $this->fake->streetName,
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                    'contactPerson' => $this->fake->name
                ],
                AddressType::DELIVERY,
            ],

            [
                [
                    // Delivery address to 'Packstation'
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => 'PACKSTATION',
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                ],
                AddressType::DELIVERY,
            ],

            [
                [
                    // Delivery address to 'Postfiliale'
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => 'POSTFILIALE',
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                ],
                AddressType::DELIVERY,
            ]
            // TODO Address Options
        ];
    }

    public function deleteAddressProvider()
    {
        return [
            [
                [
                    // Billing address with company
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => $this->fake->streetName,
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                ],
                AddressType::BILLING,
            ],

            [
                [
                    // Billing address with company
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => $this->fake->streetName,
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                ],
                AddressType::DELIVERY,
            ],
        ];
    }

    public function updateAddressProvider()
    {
        return [
            [
                [
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => 'change',
                    'name2' => 'change',
                    'name3' => 'change',
                    'name4' => 'change',
                    'address1' => 'change',
                    'address2' => 'change',
                    'postalCode' => 'change',
                    'town' => 'change',
                    'countryId' => 1,
                    'stateId' => '',
                ],
                [
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => $this->fake->streetName,
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                    'stateId' => '',
                ],
                AddressType::BILLING,
            ],

            [
                [
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => 'change',
                    'name2' => 'change',
                    'name3' => 'change',
                    'name4' => 'change',
                    'address1' => 'change',
                    'address2' => 'change',
                    'postalCode' => 'change',
                    'town' => 'change',
                    'countryId' => 1,
                    'stateId' => '',
                ],
                [
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => 'PACKSTATION',
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                    'stateId' => '',
                ],
                AddressType::DELIVERY,
            ],

            [
                [
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => 'change',
                    'name2' => 'change',
                    'name3' => 'change',
                    'name4' => 'change',
                    'address1' => 'change',
                    'address2' => 'change',
                    'postalCode' => 'change',
                    'town' => 'change',
                    'countryId' => 1,
                    'stateId' => '',
                ],
                [
                    'gender' => $this->fake->randomElement($this->genders),
                    'name1' => $this->fake->company,
                    'name2' => $this->fake->firstName,
                    'name3' => $this->fake->lastName,
                    'name4' => '',
                    'address1' => 'POSTFILIALE',
                    'address2' => $this->fake->streetAddress,
                    'postalCode' => $this->fake->postcode,
                    'town' => $this->fake->city,
                    'countryId' => 1,
                    'stateId' => '',
                ],
                AddressType::DELIVERY,
            ],
        ];
    }

    private function assertAddressFieldsAreEqual($address1, $address2)
    {
        foreach ($address1 as $key => $value) {
            // Do not compare 'contactPerson' because it is stored as a address option
            // Do not compare company
            if ($key !== 'contactPerson' && ($key !== 'gender' && $address2[$key] !== 'company')) {
                $this->assertEquals($address1[$key], $address2[$key]);
            }
        }
    }
}
