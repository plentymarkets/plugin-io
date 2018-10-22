<?php

use IO\Services\CustomerService;
use IO\Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    /** @var CustomerService $customerService */
    protected $customerService;

    protected function setUp()
    {
        parent::setUp();

        $this->customerService = pluginApp(CustomerService::class);
    }

    /** @test */
    public function it_creates_an_address()
    {
        
    }
}