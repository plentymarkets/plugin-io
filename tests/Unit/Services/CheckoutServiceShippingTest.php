<?php
/**
 * Created by PhpStorm.
 * User: lukasmatzen
 * Date: 15.01.19
 * Time: 16:56
 */

namespace IO\Tests\Unit;


use IO\Services\BasketService;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use IO\Tests\TestCase;
use Mockery;
use Plenty\Modules\Account\Contracts\AccountRepositoryContract;
use Plenty\Modules\Accounting\Contracts\AccountingLocationRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Application;

class CheckoutServiceShippingTest extends TestCase
{

    /**
     * @var Checkout $checkoutMock
     */
    private $checkoutMock;

    /**
     * @var WebstoreConfigurationService $webstoreConfigurationServiceMock
     */
    private $webstoreConfigurationServiceMock;

    /**
     * @var SessionStorageService $sessionStorageServiceMock ;
     */
    private $sessionStorageServiceMock;

    /**
     * @var AccountRepositoryContract $accountRepositoryMock
     */
    private $accountRepositoryMock;


    /**
     * @var Application $applicationMock
     */
    private $applicationMock;

    /**
     * @var ParcelServicePresetRepositoryContract $parcelServiceRepoMock
     */
    private $parcelServiceRepoMock;


    /**
     * @var CheckoutService $checkoutService
     */
    private $checkoutService;

    /**
     * @var VatService $vatServiceMock
     */
    private $vatServiceMock;

    /**
     * @var BasketService $basketServiceMock
     */
    private $basketServiceMock;

    /**
     * @var CustomerService $customerServiceMock
     */
    private $customerServiceMock;

    /**
     * @var FrontendPaymentMethodRepositoryContract $frontendPaymentMock
     */
    private $frontendPaymentMock;

    /**
     * @var CurrencyExchangeRepositoryContract $currencyExchangeRepoMock
     */
    private $currencyExchangeRepoMock;

    protected function setUp()
    {
        parent::setUp();


        $this->checkoutMock = Mockery::mock(Checkout::class);
        $this->replaceInstanceByMock(Checkout::class, $this->checkoutMock);

        $this->webstoreConfigurationServiceMock = Mockery::mock(WebstoreConfigurationService::class);
        $this->replaceInstanceByMock(WebstoreConfigurationService::class, $this->webstoreConfigurationServiceMock);

        $this->sessionStorageServiceMock = Mockery::mock(SessionStorageService::class);
        $this->replaceInstanceByMock(SessionStorageService::class, $this->sessionStorageServiceMock);

        $this->accountRepositoryMock = Mockery::mock(AccountingLocationRepositoryContract::class);
        $this->replaceInstanceByMock(AccountingLocationRepositoryContract::class, $this->accountRepositoryMock);

        $this->vatServiceMock = Mockery::mock(VatService::class);
        $this->replaceInstanceByMock(VatService::class, $this->vatServiceMock);

        $this->applicationMock = Mockery::mock(Application::class);
        $this->replaceInstanceByMock(Application::class, $this->applicationMock);

        $this->parcelServiceRepoMock = Mockery::mock(ParcelServicePresetRepositoryContract::class);
        $this->replaceInstanceByMock(ParcelServicePresetRepositoryContract::class, $this->parcelServiceRepoMock);

        $this->vatServiceMock = Mockery::mock(VatService::class);
        $this->replaceInstanceByMock(VatService::class, $this->vatServiceMock);

        $this->basketServiceMock = Mockery::mock(BasketService::class);
        $this->replaceInstanceByMock(BasketService::class, $this->basketServiceMock);

        $this->currencyExchangeRepoMock = Mockery::mock(CurrencyExchangeRepositoryContract::class);
        $this->replaceInstanceByMock(CurrencyExchangeRepositoryContract::class, $this->currencyExchangeRepoMock);

        $this->customerServiceMock = Mockery::mock(CustomerService::class);
        $this->replaceInstanceByMock(CustomerService::class, $this->customerServiceMock);

        $this->frontendPaymentMock = Mockery::mock(FrontendPaymentMethodRepositoryContract::class);
        $this->replaceInstanceByMock(FrontendPaymentMethodRepositoryContract::class, $this->frontendPaymentMock);

        $this->checkoutService = pluginApp(CheckoutService::class);

    }


    /**
     * @test
     * @dataProvider dataProviderShippingCountryId
     */
    public function it_gets_the_right_shipping_country_id($shippingCountryId)
    {

        $this->checkoutMock->shouldReceive('getShippingCountryId')->andReturn($shippingCountryId);

        $this->webstoreConfigurationServiceMock->shouldReceive('getDefaultShippingCountryId')->andReturn(0);


        $checkoutShippingCountryId = $this->checkoutService->getShippingCountryId();

        $this->assertEquals($shippingCountryId, $checkoutShippingCountryId);

    }

    /**
     * @test
     * @dataProvider dataProviderShippingProfiles
     */
    public function it_gets_the_right_shipping_profiles($shippingList)
    {


        $basket = factory(Basket::class)->make(
            [
                'currency' => 'EUR'
            ]
        );

        $this->sessionStorageServiceMock->shouldReceive('getCustomer')
            ->andReturn((object)[
                'showNetPrice' => false,
                'accountContactClassId' => 1
            ]);

         $this->sessionStorageServiceMock->shouldReceive('getLang')
            ->andReturn('de');

        $this->customerServiceMock->shouldReceive('showNetPrices')
            ->andReturn(false);

        $this->checkoutMock->shouldReceive('getShippingCountryId')->andReturn(10);

        $this->applicationMock->shouldReceive('getWebstoreId')->andReturn(1);

        $this->parcelServiceRepoMock->shouldReceive('getLastWeightedPresetCombinations')->with(Mockery::any(), Mockery::any(), Mockery::any())->andReturn($shippingList);

        $this->vatServiceMock->shouldReceive('getLocationId')->andReturn(1);

        $this->frontendPaymentMock->shouldReceive('getCurrentPaymentMethodsList')->andReturn([pluginApp(PaymentMethod::class)]);
        $this->frontendPaymentMock->shouldReceive('getPaymentMethodName')->andReturn('Invoice');
        $this->frontendPaymentMock->shouldReceive('getPaymentMethodFee')->andReturn(0.00);
        $this->frontendPaymentMock->shouldReceive('getPaymentMethodIcon')->andReturn('');
        $this->frontendPaymentMock->shouldReceive('getPaymentMethodDescription')->andReturn('');
        $this->frontendPaymentMock->shouldReceive('getPaymentMethodSourceUrl')->andReturn('');
        $this->frontendPaymentMock->shouldReceive('getPaymentMethodIsSelectable')->andReturn(true);


        $this->accountRepositoryMock->shouldReceive('getSettings')
            ->andReturn((object)[
                "showShippingVat" => true
            ]);

        $this->basketServiceMock->shouldReceive('getBasket')->andReturn($basket);

        $this->currencyExchangeRepoMock->shouldReceive('getDefaultCurrency')->andReturn('EUR');

        $shippingProfileList = $this->checkoutService->getShippingProfileList();



        if(count($shippingProfileList) > 0) {
            $this->assertEquals($shippingList[0]['parcelServicePresetId'], $shippingProfileList[0]['parcelServicePresetId']);
        }

        $this->assertEquals(count($shippingList), count($shippingProfileList));


    }




    public function dataProviderShippingCountryId()
    {

        return [
            [0],
            [10]
        ];

    }


    public function dataProviderShippingProfiles()
    {

        return [

            [
                [
                    [


                        'parcelServicePresetId' => 6,
                        'parcelServicePresetName' => 'versichertes Paket',
                        'parcelServiceId' => 101,
                        'parcelServiceName' => 'DHL',
                        'shippingAmount' => 4.9900000000000002,
                        'shippingPrivacyInformation' =>
                            [
                                0 => [
                                    'showDataPrivacyAgreementHint' => false,
                                    'id' => 6,
                                    'parcelServiceId' => 101,
                                    'parcelServiceName' => 'DHL',
                                    'parcelServiceAddress' => NULL,
                                ],
                            ],
                        'excludedPaymentMethodIds' => [],
                        'isPostOffice' => false,
                        'isParcelBox' => false,


                    ],
                    [


                        'parcelServicePresetId' => 4,
                        'parcelServicePresetName' => 'unversichertes Paket',
                        'parcelServiceId' => 99,
                        'parcelServiceName' => 'Hermes',
                        'shippingAmount' => 14.99,
                        'shippingPrivacyInformation' =>
                            [
                                0 => [
                                    'showDataPrivacyAgreementHint' => false,
                                    'id' => 4,
                                    'parcelServiceId' => 99,
                                    'parcelServiceName' => 'Hermes',
                                    'parcelServiceAddress' => NULL,
                                ],
                            ],
                        'excludedPaymentMethodIds' => [],
                        'isPostOffice' => true,
                        'isParcelBox' => false,


                    ],
                ]


            ],
            [
                []
            ]


        ];

    }
}
