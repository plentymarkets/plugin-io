<?php

namespace IO\Tests\Unit;

use IO\Constants\SessionStorageKeys;
use IO\Helper\MemoryCache;
use IO\Services\CheckoutService;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Mockery;
use IO\Tests\TestCase;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Plugin\Models\Plugin;

use Illuminate\Support\Facades\Session;
use Plenty\Modules\System\Models\WebstoreConfiguration;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class CheckoutServiceCurrencyWebstoreConfigTest extends TestCase
{

    /** @var CheckoutService $basketService  */
    protected $checkoutService;
    /** @var Plugin $pluginMock */
    protected $pluginMock;
    /** @var WebstoreConfigurationService $webstoreConfigServiceMock */
    protected $webstoreConfigServiceMock;
    /** @var WebstoreConfiguration $webstoreConfiguration */
    protected $webstoreConfigurationMock;
    /** @var Checkout $checkoutMock */
    protected $checkoutMock;
    /** @var SessionStorageService $sessionStorageServiceMock */
    protected $sessionStorageServiceMock;
    /** @var MemoryCache $memoryCacheMock */
    protected $memoryCacheMock;


    protected function setUp()
    {
        parent::setUp();

        $this->checkoutService = pluginApp(CheckoutService::class);

    }

    /** @test */
    public function check_method_get_currency()
    {

        $expectedCurrency = "USD";

        /** @var FrontendSessionStorageFactoryContract $sessionStorage */
        $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);
        $sessionStorage->getPlugin()->setValue(SessionStorageKeys::CURRENCY, $expectedCurrency);

        $currency = $this->checkoutService->getCurrency();

        $this->assertNotNull($currency);
        $this->assertEquals($expectedCurrency, $currency);

    }

}
