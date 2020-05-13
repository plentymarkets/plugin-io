<?php

namespace IO\Tests\Unit;

use IO\Builder\Order\AddressType;
use IO\Services\BasketService;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use IO\Tests\TestCase;
use Mockery;
use Plenty\Modules\Account\Address\Models\Address;

class CheckoutServiceAddressTest extends TestCase
{
    /** @var CheckoutService $checkoutService */
    protected $checkoutService;
    /** @var CustomerService $customerServiceMock */
    protected $customerServiceMock;
    /** @var BasketService $basketServiceMock */
    protected $basketServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication();

        $this->basketServiceMock = Mockery::mock(BasketService::class);
        $this->replaceInstanceByMock(BasketService::class, $this->basketServiceMock);

        $this->customerServiceMock = Mockery::mock(CustomerService::class);
        $this->replaceInstanceByMock(CustomerService::class, $this->customerServiceMock);

        $this->checkoutService = pluginApp(CheckoutService::class);
    }

    /** @test */
    public function it_sets_a_new_billing_address_id_because_the_one_returned_from_the_basket_service_is_null()
    {
        $address = factory(Address::class)->create();

        $this->basketServiceMock
            ->shouldReceive('getBillingAddressId')
            ->andReturn(null)
            ->once();

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with($address->id)
            ->andReturn($address->id)
            ->once();

        $this->customerServiceMock
            ->shouldReceive('getAddresses')
            ->with(AddressType::BILLING)
            ->andReturn([$address])
            ->once();

        $response = $this->checkoutService->getBillingAddressId();

        $this->assertEquals($response, $address->id);
    }

    /** @test */
    public function it_should_return_null_because_billing_address_returned_from_basket_and_customer_service_are_both_null()
    {
        $this->basketServiceMock
            ->shouldReceive('getBillingAddressId')
            ->andReturn(null)
            ->once();

        $this->customerServiceMock
            ->shouldReceive('getAddresses')
            ->with(AddressType::BILLING)
            ->andReturn(null)
            ->once();

        $response = $this->checkoutService->getBillingAddressId();

        $this->assertNull($response);
    }

    /** @test */
    public function it_sets_invalid_billing_address_id_negative()
    {
        $id = -123;

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with($id)
            ->andReturn($id)
            ->never();

        $this->checkoutService->setBillingAddressId($id);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_sets_invalid_billing_address_id_null()
    {
        $id = null;

        $this->basketServiceMock
            ->shouldReceive('setBillingAddressId')
            ->with($id)
            ->andReturn($id)
            ->never();

        $this->checkoutService->setBillingAddressId($id);

        $this->assertTrue(true);
    }
}
