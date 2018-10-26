<?php

namespace IO\Tests\Unit;

use IO\Builder\Order\AddressType;
use IO\Services\CustomerService;
use IO\Tests\TestCase;
use Illuminate\Support\Facades\Session;

class CustomerServiceTest extends TestCase
{

    /** @var CustomerService $customerService */
    protected $customerService;

    protected function setUp()
    {
        parent::setUp();

        $this->customerService = pluginApp(CustomerService::class);

    }

    /**
     * @test
     * @dataProvider provider
     */
    public function should_be_add_a_new_address($addressData, $addressType)
    {
        $newAddress = $this->customerService->createAddress($addressData, $addressType);

        $this->assertNotNull($newAddress);
        $this->assertInstanceOf(Address::class, $newAddress);
        $this->assertEquals($addressData['name1'], $newAddress->name1);
    }

    public function provider()
    {
        return [
            [
                [
                    "gender" => "male",
                    "name1" => "plentymarkets GmbH",
                    "name2" => "Timo",
                    "name3" => "Zenke",
                    "name4" => "oder beim Pförtner abgeben",
                    "address1" => "Bürgermeister-Brunner-Str.",
                    "address2" => "15",
                    "postalCode" => "34117",
                    "town" => "Kassel",
                    "countryId" => 1
                ],
                AddressType::BILLING
            ],

            [
                [
                    "gender" => "male",
                    "name1" => "plentymarkets GmbH",
                    "name2" => "Timo",
                    "name3" => "Zenke",
                    "name4" => "oder beim Pförtner abgeben",
                    "address1" => "Bürgermeister-Brunner-Str.",
                    "address2" => "15",
                    "postalCode" => "34117",
                    "town" => "Kassel",
                    "countryId" => 1
                ],
                AddressType::DELIVERY
            ],
        ];
    }

}
