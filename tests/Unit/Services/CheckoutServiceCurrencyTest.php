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
class CheckoutServiceCurrencyTest extends TestCase
{

    /** @var CheckoutService $basketService  */
    protected $checkoutService;
    /** @var FrontendSessionStorageFactoryContract */
    protected $sessionStorageMock;
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

        $this->createApplication();

        $this->sessionStorageMock = Mockery::mock(FrontendSessionStorageFactoryContract::class);
        app()->instance(FrontendSessionStorageFactoryContract::class, $this->sessionStorageMock);

        $this->pluginMock = Mockery::mock(Plugin::class);
        app()->instance(Plugin::class, $this->pluginMock);

        $this->webstoreConfigurationMock = Mockery::mock(WebstoreConfiguration::class);
        app()->instance(WebstoreConfiguration::class, $this->webstoreConfigurationMock);

        $this->webstoreConfigServiceMock = Mockery::mock(WebstoreConfigurationService::class);
        app()->instance(WebstoreConfigurationService::class, $this->webstoreConfigServiceMock);

        $this->checkoutMock = Mockery::mock(Checkout::class);
        app()->instance(Checkout::class, $this->checkoutMock);

        $this->sessionStorageServiceMock = Mockery::mock(SessionStorageService::class);
        app()->instance(SessionStorageService::class, $this->sessionStorageServiceMock);

        $this->checkoutService = pluginApp(CheckoutService::class);

    }

    /** @test */
    public function it_returns_the_currency_from_session_storage()
    {

        $expectedCurrency = "USD";

        $this->pluginMock->shouldReceive('getValue')->with(SessionStorageKeys::CURRENCY)->andReturn($expectedCurrency);
        $this->sessionStorageMock->shouldReceive('getPlugin')->andReturn($this->pluginMock);

        $currency = $this->checkoutService->getCurrency();

        $this->assertNotNull($currency);
        $this->assertEquals($expectedCurrency, $currency);

    }


    /** @test */
    public function it_returns_the_currency_not_from_session_storage_and_webstore_config()
    {

        $webstoreConfiguration = factory(WebstoreConfiguration::class)->make();

        $expectedCurrency = "EUR";

        $this->pluginMock->shouldReceive('getValue')->with(SessionStorageKeys::CURRENCY)->andReturn(null);
        $this->pluginMock->shouldReceive('setValue')->andReturn();
        $this->sessionStorageMock->shouldReceive('getPlugin')->andReturn($this->pluginMock);

        $this->sessionStorageServiceMock->shouldReceive("getLang")->andReturn("de");

        $this->webstoreConfigServiceMock->shouldReceive('getWebstoreConfig')->andReturn($webstoreConfiguration);

        $this->checkoutMock->shouldReceive('setCurrency')->andReturn();

        $currency = $this->checkoutService->getCurrency();

        $this->assertNotNull($currency);
        $this->assertEquals($expectedCurrency, $currency);

    }

    /** @test */
    public function it_returns_the_currency_not_from_session_storage_and_not_from_the_webstore_config()
    {

        $webstoreConfiguration = factory(WebstoreConfiguration::class)->make([
            "defaultCurrencyList" => ""
        ]);

        $expectedCurrency = "EUR";

        $this->pluginMock->shouldReceive('getValue')->with(SessionStorageKeys::CURRENCY)->andReturn(null);
        $this->pluginMock->shouldReceive('setValue')->andReturn();
        $this->sessionStorageMock->shouldReceive('getPlugin')->andReturn($this->pluginMock);

        Session::shouldReceive('getLang')
            ->andReturn("");

        $this->webstoreConfigServiceMock->shouldReceive('getWebstoreConfig')->andReturn($webstoreConfiguration);

        $this->checkoutMock->shouldReceive('setCurrency')->andReturn();

        $currency = $this->checkoutService->getCurrency();

        $this->assertNotNull($currency);
        $this->assertEquals($expectedCurrency, $currency);

    }

}
