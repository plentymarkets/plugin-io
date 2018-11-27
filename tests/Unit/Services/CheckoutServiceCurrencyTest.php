<?php

namespace IO\Tests\Unit;

use Faker\Generator;
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

    /** @var CheckoutService $basketService */
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
    /** @var Generator $faker */
    protected $faker;


    protected function setUp()
    {
        parent::setUp();

        $this->createApplication();

        $this->sessionStorageMock = Mockery::mock(FrontendSessionStorageFactoryContract::class);
        $this->replaceInstanceByMock(FrontendSessionStorageFactoryContract::class, $this->sessionStorageMock);

        $this->pluginMock = Mockery::mock(Plugin::class);
        $this->replaceInstanceByMock(Plugin::class, $this->pluginMock);

        $this->webstoreConfigurationMock = $this->mockWebstoreConfiguration();

        $this->webstoreConfigServiceMock = Mockery::mock(WebstoreConfigurationService::class);
        $this->replaceInstanceByMock(WebstoreConfigurationService::class, $this->webstoreConfigServiceMock);

        $this->checkoutMock = Mockery::mock(Checkout::class);
        $this->replaceInstanceByMock(Checkout::class, $this->checkoutMock);

        $this->sessionStorageServiceMock = Mockery::mock(SessionStorageService::class);
        $this->replaceInstanceByMock(SessionStorageService::class, $this->sessionStorageServiceMock);

        $this->checkoutService = pluginApp(CheckoutService::class);

        $this->faker = pluginApp(Generator::class);

    }

    /** @test */
    public function it_returns_the_currency_from_session_storage()
    {

        $expectedCurrency = $this->fake->currencyCode;

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

        $expectedCurrency = $webstoreConfiguration->defaultCurrency;

        $this->pluginMock->shouldReceive('getValue')->with(SessionStorageKeys::CURRENCY)->andReturn(null);
        $this->pluginMock->shouldReceive('setValue')->andReturn();
        $this->sessionStorageMock->shouldReceive('getPlugin')->andReturn($this->pluginMock);

        $this->sessionStorageServiceMock->shouldReceive("getLang")->andReturn($webstoreConfiguration->defaultLanguage);

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
            "defaultCurrencyList" => "",
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
