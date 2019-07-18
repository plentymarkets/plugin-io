<?php //strict

namespace IO\Services;

use IO\Builder\Order\AddressType;
use IO\Constants\SessionStorageKeys;
use IO\Events\Checkout\CheckoutReadonlyChanged;
use IO\Helper\LanguageMap;
use IO\Helper\MemoryCache;
use Plenty\Modules\Accounting\Contracts\AccountingLocationRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Frontend\Events\ValidateCheckoutEvent;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Order\Currency\Contracts\CurrencyRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

/**
 * Class CheckoutService
 * @package IO\Services
 */
class CheckoutService
{
    use MemoryCache;
    use Loggable;

    /**
     * @var FrontendPaymentMethodRepositoryContract
     */
    private $frontendPaymentMethodRepository;
    /**
     * @var Checkout
     */
    private $checkout;
    /**
     * @var BasketRepositoryContract
     */
    private $basketRepository;
    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private $sessionStorage;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var ParcelServicePresetRepositoryContract
     */
    private $parcelServicePresetRepo;

    /**
     * @var CurrencyExchangeRepositoryContract
     */
    private $currencyExchangeRepo;

    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * @var SessionStorageService
     */
    private $sessionStorageService;

    /**
     * @var WebstoreConfigurationService
     */
    private $webstoreConfigurationService;

    /**
     * CheckoutService constructor.
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
     * @param Checkout $checkout
     * @param BasketRepositoryContract $basketRepository
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param CustomerService $customerService
     * @param ParcelServicePresetRepositoryContract $parcelServicePresetRepo
     * @param CurrencyExchangeRepositoryContract $currencyExchangeRepo
     * @param BasketService $basketService
     * @param SessionStorageService $sessionStorageService
     * @param WebstoreConfigurationService $webstoreConfigurationService
     */
    public function __construct(
        FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository,
        Checkout $checkout,
        BasketRepositoryContract $basketRepository,
        FrontendSessionStorageFactoryContract $sessionStorage,
        CustomerService $customerService,
        ParcelServicePresetRepositoryContract $parcelServicePresetRepo,
        CurrencyExchangeRepositoryContract $currencyExchangeRepo,
        BasketService $basketService,
        SessionStorageService $sessionStorageService,
        WebstoreConfigurationService $webstoreConfigurationService)
    {
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
        $this->checkout                        = $checkout;
        $this->basketRepository                = $basketRepository;
        $this->sessionStorage                  = $sessionStorage;
        $this->customerService                 = $customerService;
        $this->parcelServicePresetRepo         = $parcelServicePresetRepo;
        $this->currencyExchangeRepo            = $currencyExchangeRepo;
        $this->basketService                   = $basketService;
        $this->sessionStorageService           = $sessionStorageService;
        $this->webstoreConfigurationService    = $webstoreConfigurationService;
    }

    /**
     * Get the relevant data for the checkout
     * @param bool $retry   Try loading checkout again on failure (e.g. problems during calculating totals)
     * @return array
     */
    public function getCheckout($retry = true): array
    {
        try
        {
            return [
                "currency"            => $this->getCurrency(),
                "currencyList"        => $this->getCurrencyList(),
                "methodOfPaymentId"   => $this->getMethodOfPaymentId(),
                "methodOfPaymentList" => $this->getMethodOfPaymentList(),
                "shippingCountryId"   => $this->getShippingCountryId(),
                "shippingProfileId"   => $this->getShippingProfileId(),
                "shippingProfileList" => $this->getShippingProfileList(),
                "deliveryAddressId" => $this->getDeliveryAddressId(),
                "billingAddressId" => $this->getBillingAddressId(),
                "paymentDataList" => $this->getCheckoutPaymentDataList(),
                "maxDeliveryDays" => $this->getMaxDeliveryDays(),
                "readOnly"            => $this->getReadOnlyCheckout()
            ];
        }
        catch(\Exception $e)
        {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->error($e->getMessage(), $e->getCode());
            return $retry ? $this->getCheckout(false) : null;
        }
    }

    /**
     * Get the current currency from the session
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->fromMemoryCache(
            "currency",
            function() {
                $currency = (string)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::CURRENCY);
                if ($currency === null || $currency === "") {
                    /** @var SessionStorageService $sessionService */
                    $sessionService = pluginApp(SessionStorageService::class);

                    /** @var WebstoreConfigurationService $webstoreConfig */
                    $webstoreConfig = pluginApp(WebstoreConfigurationService::class);

                    $currency = 'EUR';

                    if (
                        is_array($webstoreConfig->getWebstoreConfig()->defaultCurrencyList) &&
                        array_key_exists($sessionService->getLang(), $webstoreConfig->getWebstoreConfig()->defaultCurrencyList)
                    ) {
                        $currency = $webstoreConfig->getWebstoreConfig()->defaultCurrencyList[$sessionService->getLang()];
                    }
                    $this->setCurrency($currency);
                }
                return $currency;
            }
        );
    }

    /**
     * Set the current currency from the session
     * @param string $currency
     */
    public function setCurrency(string $currency)
    {
        $this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::CURRENCY, $currency);
        $this->checkout->setCurrency($currency);
    }

    public function getCurrencyList()
    {
        /** @var CurrencyRepositoryContract $currencyRepository */
        $currencyRepository = pluginApp( CurrencyRepositoryContract::class );

        $currencyList = [];
        $locale = LanguageMap::getLocale();

        foreach( $currencyRepository->getCurrencyList() as $currency )
        {
            $formatter = numfmt_create(
                $locale . "@currency=" . $currency->currency,
                \NumberFormatter::CURRENCY
            );
            $currencyList[] = [
                "name" => $currency->currency,
                "symbol" => $formatter->getSymbol( \NumberFormatter::CURRENCY_SYMBOL )
            ];
        }
        return $currencyList;
    }


    public function getCurrencyData()
    {
        return $this->fromMemoryCache(
            "currencyData",
            function() {
                $currency = $this->getCurrency();
                $locale = LanguageMap::getLocale();

                $formatter = numfmt_create(
                    $locale . "@currency=" . $currency,
                    \NumberFormatter::CURRENCY
                );

                return [
                    "name" => $currency,
                    "symbol" => $formatter->getSymbol( \NumberFormatter::CURRENCY_SYMBOL )
                ];
            }
        );
    }

    public function getCurrencyPattern()
    {
        $currency = $this->getCurrency();
        $locale = LanguageMap::getLocale();
        $configRepository = pluginApp( ConfigRepository::class );

        $formatter = numfmt_create(
            $locale . "@currency=" . $currency,
            \NumberFormatter::CURRENCY
        );

        if($configRepository->get('IO.format.use_locale_currency_format') === "0")
        {
            $formatter->setSymbol(
                \NumberFormatter::MONETARY_SEPARATOR_SYMBOL,
                $configRepository->get('IO.format.separator_decimal')
            );
            $formatter->setSymbol(
                \NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL,
                $configRepository->get('IO.format.separator_thousands')
            );
            $formatter->setAttribute(
                \NumberFormatter::FRACTION_DIGITS,
                $configRepository->get('IO.format.number_decimals', 2)
            );
        }

        return [
            "separator_decimal"   => $formatter->getSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL),
            "separator_thousands" => $formatter->getSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL),
            "number_decimals"     => $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS),
            "pattern"             => $formatter->getPattern()
        ];
    }

    /**
     * Get the ID of the current payment method
     * @return int
     */
    public function getMethodOfPaymentId()
    {
        $methodOfPaymentID = (int)$this->checkout->getPaymentMethodId();

        $methodOfPaymentList = $this->getMethodOfPaymentList();
        $methodOfPaymentExpressCheckoutList = $this->getMethodOfPaymentExpressCheckoutList();
        $methodOfPaymentList = array_merge($methodOfPaymentList, $methodOfPaymentExpressCheckoutList);

        $methodOfPaymentValid = false;
        foreach($methodOfPaymentList as $methodOfPayment)
        {
            if((int)$methodOfPaymentID == $methodOfPayment->id)
            {
                $methodOfPaymentValid = true;
            }
        }

        if ($methodOfPaymentID === null || !$methodOfPaymentValid)
        {
            $methodOfPaymentID   = $methodOfPaymentList[0]->id;

            if(!is_null($methodOfPaymentID))
            {
                $this->setMethodOfPaymentId($methodOfPaymentID);
            }
        }

        return $methodOfPaymentID;
    }

    /**
     * Set the ID of the current payment method
     * @param int $methodOfPaymentID
     */
    public function setMethodOfPaymentId(int $methodOfPaymentID)
    {
        $this->checkout->setPaymentMethodId($methodOfPaymentID);
        $this->sessionStorage->getPlugin()->setValue('MethodOfPaymentID', $methodOfPaymentID);
    }

    /**
     * Prepare the payment
     * @return array
     */
    public function preparePayment(): array
    {
        $validateCheckoutEvent = $this->checkout->validateCheckout();
        if ($validateCheckoutEvent instanceof ValidateCheckoutEvent && !empty($validateCheckoutEvent->getErrorKeysList())) {
            $dispatcher = pluginApp(Dispatcher::class);
            if ($dispatcher instanceof Dispatcher) {
                $dispatcher->fire(pluginApp(AfterBasketChanged::class), []);
            }

            $translator = pluginApp(Translator::class);
            if ($translator instanceof Translator) {
                $errors = [];
                $webstoreConfiguration = $this->webstoreConfigurationService->getWebstoreConfig();
                foreach ($validateCheckoutEvent->getErrorKeysList() as $errorKey) {
                    switch($errorKey) {
                        case 'frontend/checkout/validation.minimum_order_value':
                            $params = [
                                'minimumOrderValue' => $webstoreConfiguration->minimumOrderValue,
                                'currency' => $webstoreConfiguration->defaultCurrency,
                            ];
                            $errors[] = $translator->trans('Ceres::Template.errorMinimumOrderValueNotReached', $params);
                            break;
                        default:
                            $errors[] = $translator->trans($errorKey);
                    }
                }

                $result = array(
                    "type" => GetPaymentMethodContent::RETURN_TYPE_ERROR,
                    "value" => implode('<br>', $errors)
                );

                $this->getLogger(__CLASS__)->error(
                    "IO::Debug.CheckoutService_preparePaymentFailed",
                    $result
                );

                return $result;
            }
        }

        $mopId = $this->getMethodOfPaymentId();
        $result = pluginApp(PaymentMethodRepositoryContract::class)->preparePaymentMethod($mopId);
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.CheckoutService_paymentPrepared",
            [
                "type" => $result["type"],
                "value" => $result["value"],
                "paymentId" => $mopId,
                "basket" => pluginApp(BasketService::class)->getBasket()
            ]
        );

        return $result;
    }

    /**
     * List all available payment methods
     * @return array
     */
    public function getMethodOfPaymentList(): array
    {
        return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsList();
    }

    /**
     * List all payment methods available for express checkout
     * @return array
     */
    public function getMethodOfPaymentExpressCheckoutList(): array
    {
        return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsForExpressCheckout();
    }

    /**
     * Get a list of the payment method data
     * @return array
     */
    public function getCheckoutPaymentDataList(): array
    {
        $paymentDataList = array();
        $mopList         = $this->getMethodOfPaymentList();
        $lang            = $this->sessionStorageService->getLang();
        foreach ($mopList as $paymentMethod) {
            $paymentData                = array();
            $paymentData['id']          = $paymentMethod->id;
            $paymentData['name']        = $this->frontendPaymentMethodRepository->getPaymentMethodName($paymentMethod, $lang);
            $paymentData['fee']         = $this->frontendPaymentMethodRepository->getPaymentMethodFee($paymentMethod);
            $paymentData['icon']        = $this->frontendPaymentMethodRepository->getPaymentMethodIcon($paymentMethod, $lang);
            $paymentData['description'] = $this->frontendPaymentMethodRepository->getPaymentMethodDescription($paymentMethod, $lang);
            $paymentData['sourceUrl']   = $this->frontendPaymentMethodRepository->getPaymentMethodSourceUrl($paymentMethod);
            $paymentData['key']         = $paymentMethod->pluginKey;
            $paymentData['isSelectable']= $this->frontendPaymentMethodRepository->getPaymentMethodIsSelectable($paymentMethod);
            $paymentDataList[]          = $paymentData;
        }
        return $paymentDataList;
    }

    /**
     * Get the shipping profile list
     * @return array
     */
    public function getShippingProfileList()
    {
        return $this->fromMemoryCache('shippingProfileList.' . $this->getShippingCountryId(), function()
        {
            /** @var AccountingLocationRepositoryContract $accountRepo*/
            $accountRepo = pluginApp(AccountingLocationRepositoryContract::class);
            /** @var VatService $vatService*/
            $vatService = pluginApp(VatService::class);
            $showNetPrice   = $this->sessionStorageService->getCustomer()->showNetPrice;

            $list = $this->parcelServicePresetRepo->getLastWeightedPresetCombinations($this->basketRepository->load(), $this->sessionStorageService->getCustomer()->accountContactClassId);

            $locationId = $vatService->getLocationId($this->getShippingCountryId());
            $accountSettings = $accountRepo->getSettings($locationId);

            if ($showNetPrice && !(bool)$accountSettings->showShippingVat) {

                $maxVatValue = $this->basketService->getMaxVatValue();

                if (is_array($list)) {
                    foreach ($list as $key => $shippingProfile) {
                        if (isset($shippingProfile['shippingAmount'])) {
                            $list[$key]['shippingAmount'] = (100.0 * $shippingProfile['shippingAmount']) / (100.0 + $maxVatValue);
                        }
                    }
                }

                $basket = $this->basketService->getBasket();
                if ($basket->currency !== $this->currencyExchangeRepo->getDefaultCurrency())
                {
                    if (is_array($list))
                    {
                        foreach ($list as $key => $shippingProfile)
                        {
                            if (isset($shippingProfile['shippingAmount']))
                            {
                                $list[$key]['shippingAmount'] = $this->currencyExchangeRepo->convertFromDefaultCurrency($basket->currency, $list[$key]['shippingAmount']);
                            }
                        }
                    }
                }
            }

            return $list;
        });
    }

    /**
     * Get the ID of the current shipping country
     * @return int
     */
    public function getShippingCountryId()
    {
        $currentShippingCountryId = (int)$this->checkout->getShippingCountryId();
        if($currentShippingCountryId <= 0)
        {
            return pluginApp(WebstoreConfigurationService::class)->getDefaultShippingCountryId();
        }

        return $currentShippingCountryId;
    }

    /**
     * Set the ID of thevcurrent shipping country
     * @param int $shippingCountryId
     */
    public function setShippingCountryId(int $shippingCountryId)
    {
        $this->checkout->setShippingCountryId($shippingCountryId);
    }

    /**
     * Get the ID of the current shipping profile
     * @return int
     */
    public function getShippingProfileId(): int
    {
        $basket = $this->basketRepository->load();
        return $basket->shippingProfileId;
    }

    /**
     * Set the ID of the current shipping profile
     * @param int $shippingProfileId
     */
    public function setShippingProfileId(int $shippingProfileId)
    {
        $this->checkout->setShippingProfileId($shippingProfileId);
    }

    /**
     * Get the ID of the current delivery address
     * @return int
     */
    public function getDeliveryAddressId()
    {
        return (int)$this->basketService->getDeliveryAddressId();
    }

    /**
     * Set the ID of the current delivery address
     * @param int $deliveryAddressId
     */
    public function setDeliveryAddressId($deliveryAddressId)
    {
        $this->basketService->setDeliveryAddressId($deliveryAddressId);
    }

    /**
     * Get the ID of the current invoice address
     * @return int
     */
    public function getBillingAddressId()
    {

        $billingAddressId = $this->basketService->getBillingAddressId();

        if (is_null($billingAddressId) || (int)$billingAddressId <= 0)
        {
            $addresses = $this->customerService->getAddresses(AddressType::BILLING);
            if (count($addresses) > 0)
            {
                $billingAddressId = $addresses[0]->id;
                $this->setBillingAddressId($billingAddressId);
            }
        }

        return $billingAddressId;
    }

    /**
     * Set the ID of the current invoice address
     * @param int $billingAddressId
     */
    public function setBillingAddressId($billingAddressId)
    {
        if ((int)$billingAddressId > 0) {
            $this->basketService->setBillingAddressId($billingAddressId);
        }
    }

    public function setDefaultShippingCountryId()
    {
        /** @var WebstoreConfigurationService $webstoreConfig */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        $defaultShippingCountryId = $webstoreConfigService->getDefaultShippingCountryId();

        $this->setShippingCountryId($defaultShippingCountryId);
    }

    public function getMaxDeliveryDays()
    {
        /** @var ShippingService $shippingService */
        $shippingService = pluginApp(ShippingService::class);
        return $shippingService->getMaxDeliveryDays();
    }

    public function setReadOnlyCheckout($readonly)
    {
        if ( $this->getReadOnlyCheckout() !== $readonly )
        {
            /** @var Dispatcher $dispatcher */
            $dispatcher = pluginApp(Dispatcher::class);
            $dispatcher->fire(pluginApp(CheckoutReadonlyChanged::class, ['isReadonly' => $readonly]));
            $this->sessionStorageService->setSessionValue(SessionStorageKeys::READONLY_CHECKOUT, $readonly);
        }

    }

    public function getReadOnlyCheckout()
    {
        $readOnlyCheckout = $this->sessionStorageService->getSessionValue(SessionStorageKeys::READONLY_CHECKOUT);
        return ( !is_null($readOnlyCheckout) ? $readOnlyCheckout : false );
    }
}
