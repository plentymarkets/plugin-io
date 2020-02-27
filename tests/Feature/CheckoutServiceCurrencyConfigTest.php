<?php

namespace IO\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use IO\Services\CheckoutService;
use IO\Tests\TestCase;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Plugin\PluginSet\Models\PluginSet;
use Plenty\Modules\System\Models\Webstore;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\ConfigRepository;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class CheckoutServiceCurrencyConfigTest extends TestCase
{
    use RefreshDatabase;

    /** @var CheckoutService $basketService */
    protected $checkoutService;

    /** @var CheckoutRepositoryContract $checkoutRepository */
    protected $checkoutRepository;

    /** @var SessionStorageRepositoryContract $sessionStorageRepository */
    protected $sessionStorageRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->createApplication();

        $this->sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);
        $this->checkoutService = pluginApp(CheckoutService::class);
        $this->checkoutRepository = pluginApp(CheckoutRepositoryContract::class);
    }

    /** @test */
    public function check_method_get_currency_session_storage()
    {

        $expectedCurrency = $this->fake->currencyCode;

        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::CURRENCY, $expectedCurrency);

        $currency = $this->checkoutRepository->getCurrency();

        $this->assertNotNull($currency);
        $this->assertEquals($expectedCurrency, $currency);


    }

    /** @test */
    public function check_method_get_currency_webstore_config()
    {

        /** @var PluginSet $pluginSet */
        $pluginSet = factory(PluginSet::class)->create();

        factory(Webstore::class)->create(
            [
                "pluginSetId" => $pluginSet->id
            ]
        );
        $expectedCurrency = "EUR";

        $currency = $this->checkoutRepository->getCurrency();

        $this->assertNotNull($currency);
        $this->assertEquals($expectedCurrency, $currency);

    }

    /** @test */
    public function check_method_get_currency_list()
    {
        $currencyList = $this->checkoutService->getCurrencyList();

        $this->assertNotNull($currencyList);
        $this->assertTrue(is_array($currencyList));
    }

    /** @test */
    public function check_method_get_currency_data()
    {
        $expectedCurrency = "EUR";
        $expectedSymbol = "€";

        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::CURRENCY, $expectedCurrency);


        $currencyData = $this->checkoutService->getCurrencyData();

        $this->assertNotNull($currencyData);
        $this->assertTrue(is_array($currencyData));
        $this->assertEquals($expectedCurrency, $currencyData['name']);
        $this->assertEquals($expectedSymbol, $currencyData['symbol']);

    }

    /** @test */
    public function check_method_get_currency_pattern_without_config()
    {
        $expectedSeparatorDecimal = ",";
        $expectedSeparatorThousands = ".";
        $expectedPattern = "#,##0.00 ¤";

        /** @var FrontendSessionStorageFactoryContract $sessionStorage */
        $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);
        $sessionStorage->getPlugin()->setValue(SessionStorageRepositoryContract::CURRENCY, "USD");
        $sessionStorage->getLocaleSettings()->language = 'de';
        $currencyPattern = $this->checkoutService->getCurrencyPattern();

        $this->assertNotNull($currencyPattern);
        $this->assertTrue(is_array($currencyPattern));
        $this->assertEquals($expectedSeparatorDecimal, $currencyPattern['separator_decimal']);
        $this->assertEquals($expectedSeparatorThousands, $currencyPattern['separator_thousands']);
        $this->assertEquals($expectedPattern, $currencyPattern['pattern']);

    }

    /** @test */
    public function check_method_get_currency_pattern_with_config_and_locale_en()
    {
        $expectedSeparatorDecimal = ".";
        $expectedSeparatorThousands = ",";
        $expectedPattern = "¤ #,##0.00";

        /** @var FrontendSessionStorageFactoryContract $sessionStorage */
        $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);
        $sessionStorage->getPlugin()->setValue(SessionStorageRepositoryContract::CURRENCY, "USD");
        $sessionStorage->getLocaleSettings()->language = 'en';

        /** @var ConfigRepository $configRepository */
        $configRepository = pluginApp(ConfigRepository::class);
        $configRepository->set('IO.format.use_locale_currency_format', "0");
        $configRepository->set('IO.format.separator_decimal', $expectedSeparatorDecimal);
        $configRepository->set('IO.format.separator_thousands', $expectedSeparatorThousands);


        $currencyPattern = $this->checkoutService->getCurrencyPattern();

        $this->assertNotNull($currencyPattern);
        $this->assertTrue(is_array($currencyPattern));
        $this->assertEquals($expectedSeparatorDecimal, $currencyPattern['separator_decimal']);
        $this->assertEquals($expectedSeparatorThousands, $currencyPattern['separator_thousands']);
        $this->assertEquals($expectedPattern, $currencyPattern['pattern']);

    }

}
