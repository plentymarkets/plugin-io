<?php //strict

namespace IO\Services;

use IO\Builder\Order\AddressType;
use IO\Events\Checkout\CheckoutReadonlyChanged;
use IO\Helper\ArrayHelper;
use IO\Helper\MemoryCache;
use Plenty\Modules\Accounting\Contracts\AccountingLocationRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
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
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;
use IO\Helper\Utils;

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

    /** @var SessionStorageRepositoryContract */
    private $sessionStorageRepository;

    /** @var WebstoreConfigurationRepositoryContract */
    private $webstoreConfigurationRepository;

    /** @var CheckoutRepositoryContract $checkoutRepository */
    private $checkoutRepository;

    /** @var ContactRepositoryContract $contactRepository */
    private $contactRepository;

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
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     * @param WebstoreConfigurationService $webstoreConfigurationService
     * @param Dispatcher $dispatcher
     * @param CheckoutRepositoryContract $checkoutRepository
     * @param ContactRepositoryContract $contactRepository
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
        SessionStorageRepositoryContract $sessionStorageRepository,
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository,
        Dispatcher $dispatcher,
        CheckoutRepositoryContract $checkoutRepository,
        ContactRepositoryContract $contactRepository
    ) {
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
        $this->checkout = $checkout;
        $this->basketRepository = $basketRepository;
        $this->sessionStorage = $sessionStorage;
        $this->customerService = $customerService;
        $this->parcelServicePresetRepo = $parcelServicePresetRepo;
        $this->currencyExchangeRepo = $currencyExchangeRepo;
        $this->basketService = $basketService;
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;
        $this->checkoutRepository = $checkoutRepository;
        $this->contactRepository = $contactRepository;

        $dispatcher->listen(
            AfterBasketChanged::class,
            function ($event) {
                $this->resetMemoryCache('methodOfPaymentList');
                $this->resetMemoryCache('paymentDataList');
            }
        );
    }

    /**
     * Get the relevant data for the checkout
     * @param bool $retry Try loading checkout again on failure (e.g. problems during calculating totals)
     * @return array
     */
    public function getCheckout($retry = true): ?array
    {
        try {
            return [
                "currency" => $this->checkoutRepository->getCurrency(),
                "currencyList" => $this->getCurrencyList(),
                "methodOfPaymentId" => $this->getMethodOfPaymentId(),
                "methodOfPaymentList" => $this->getMethodOfPaymentList(),
                "shippingCountryId" => $this->getShippingCountryId(),
                "shippingProfileId" => $this->getShippingProfileId(),
                "shippingProfileList" => $this->getShippingProfileList(),
                "deliveryAddressId" => $this->getDeliveryAddressId(),
                "billingAddressId" => $this->getBillingAddressId(),
                "paymentDataList" => $this->getCheckoutPaymentDataList(),
                "maxDeliveryDays" => $this->getMaxDeliveryDays(),
                "readOnly" => $this->getReadOnlyCheckout()
            ];
        } catch (\Exception $e) {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->error($e->getMessage(), $e->getCode());
            return $retry ? $this->getCheckout(false) : null;
        }
    }

    /**
     * Get the current currency from the session
     * @return string
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract::getCurrency()
     */
    public function getCurrency(): string
    {
        return $this->checkoutRepository->getCurrency();
    }

    /**
     * Set the current currency from the session
     * @param string $currency
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract::setCurrency()
     */
    public function setCurrency(string $currency)
    {
        $this->checkoutRepository->setCurrency($currency);
    }

    public function getCurrencyList()
    {
        /** @var CurrencyRepositoryContract $currencyRepository */
        $currencyRepository = pluginApp(CurrencyRepositoryContract::class);

        $currencyList = [];
        /** @var LocalizationRepositoryContract $localizationRepository */
        $localizationRepository = pluginApp(LocalizationRepositoryContract::class);
        $locale = $localizationRepository->getLocale();

        foreach ($currencyRepository->getCurrencyList() as $currency) {
            $formatter = numfmt_create(
                $locale . "@currency=" . $currency->currency,
                \NumberFormatter::CURRENCY
            );
            $currencyList[] = [
                "name" => $currency->currency,
                "symbol" => $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
            ];
        }
        return $currencyList;
    }


    public function getCurrencyData()
    {
        return $this->fromMemoryCache(
            "currencyData",
            function () {
                $currency = $this->checkoutRepository->getCurrency();
                /** @var LocalizationRepositoryContract $localizationRepository */
                $localizationRepository = pluginApp(LocalizationRepositoryContract::class);
                $locale = $localizationRepository->getLocale();

                $formatter = numfmt_create(
                    $locale . "@currency=" . $currency,
                    \NumberFormatter::CURRENCY
                );

                return [
                    "name" => $currency,
                    "symbol" => $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
                ];
            }
        );
    }

    public function getCurrencyPattern()
    {
        $currency = $this->checkoutRepository->getCurrency();
        /** @var LocalizationRepositoryContract $localizationRepository */
        $localizationRepository = pluginApp(LocalizationRepositoryContract::class);
        $locale = $localizationRepository->getLocale();
        $configRepository = pluginApp(ConfigRepository::class);

        $formatter = numfmt_create(
            $locale . "@currency=" . $currency,
            \NumberFormatter::CURRENCY
        );

        if ($configRepository->get('IO.format.use_locale_currency_format') === "0") {
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

        // Check if pattern has ISO Code in front
        $pattern = $formatter->getPattern();
        if (mb_substr($pattern, 0, 1, "UTF-8") === "\u{00A4}") {
            // Insert a space after the beginning character
            $pattern = mb_substr($pattern, 0, 1) . " " . mb_substr($pattern, 1);
        }

        return [
            "separator_decimal" => $formatter->getSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL),
            "separator_thousands" => $formatter->getSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL),
            "number_decimals" => $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS),
            "pattern" => $pattern
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
        foreach ($methodOfPaymentList as $methodOfPayment) {
            if ((int)$methodOfPaymentID == $methodOfPayment->id) {
                $methodOfPaymentValid = true;
            }
        }

        if ($methodOfPaymentID === null || !$methodOfPaymentValid) {
            $methodOfPaymentID = $methodOfPaymentList[0]->id;

            if (!is_null($methodOfPaymentID)) {
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
        $this->sessionStorageRepository->setSessionValue('MethodOfPaymentID', $methodOfPaymentID);
    }

    /**
     * Prepare the payment
     * @return array
     */
    public function preparePayment(): array
    {
        /** @var PaymentMethodRepositoryContract $paymentMethodRepo */
        $paymentMethodRepo = pluginApp(PaymentMethodRepositoryContract::class);

        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);

        $validateCheckoutEvent = $this->checkout->validateCheckout();
        if ($validateCheckoutEvent instanceof ValidateCheckoutEvent && !empty(
            $validateCheckoutEvent->getErrorKeysList()
            )) {
            $dispatcher = pluginApp(Dispatcher::class);
            if ($dispatcher instanceof Dispatcher) {
                $dispatcher->fire(pluginApp(AfterBasketChanged::class), []);
            }

            $translator = pluginApp(Translator::class);
            if ($translator instanceof Translator) {
                $errors = [];
                $webstoreConfiguration = $this->webstoreConfigurationRepository->getWebstoreConfiguration();
                foreach ($validateCheckoutEvent->getErrorKeysList() as $errorKey) {
                    switch ($errorKey) {
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
        $result = $paymentMethodRepo->preparePaymentMethod($mopId);
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.CheckoutService_paymentPrepared",
            [
                "type" => $result["type"],
                "value" => $result["value"],
                "paymentId" => $mopId,
                "basket" => $basketService->getBasket()
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
        return $this->fromMemoryCache(
            'methodOfPaymentList',
            function () {
                return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsList();
            }
        );
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
        return $this->fromMemoryCache(
            'paymentDataList',
            function () {
                $paymentDataList = array();
                $mopList = $this->getMethodOfPaymentList();
                $lang = Utils::getLang();
                foreach ($mopList as $paymentMethod) {
                    $paymentData = array();
                    $paymentData['id'] = $paymentMethod->id;
                    $paymentData['name'] = $this->frontendPaymentMethodRepository->getPaymentMethodName(
                        $paymentMethod,
                        $lang
                    );
                    $paymentData['fee'] = $this->frontendPaymentMethodRepository->getPaymentMethodFee($paymentMethod);
                    $paymentData['icon'] = $this->frontendPaymentMethodRepository->getPaymentMethodIcon(
                        $paymentMethod,
                        $lang
                    );
                    $paymentData['description'] = $this->frontendPaymentMethodRepository->getPaymentMethodDescription(
                        $paymentMethod,
                        $lang
                    );
                    $paymentData['sourceUrl'] = $this->frontendPaymentMethodRepository->getPaymentMethodSourceUrl(
                        $paymentMethod
                    );
                    $paymentData['isSelectable'] = $this->frontendPaymentMethodRepository->getPaymentMethodIsSelectable(
                        $paymentMethod
                    );
                    $paymentData['key'] = $paymentMethod->pluginKey;
                    $paymentDataList[] = $paymentData;
                }
                return $paymentDataList;
            }
        );
    }

    /**
     * Get the shipping profile list
     * @return array
     */
    public function getShippingProfileList()
    {
        return $this->fromMemoryCache(
            'shippingProfileList.' . $this->getShippingCountryId(),
            function () {
                /** @var AccountingLocationRepositoryContract $accountRepo */
                $accountRepo = pluginApp(AccountingLocationRepositoryContract::class);
                /** @var VatService $vatService */
                $vatService = pluginApp(VatService::class);
                $showNetPrice = $this->contactRepository->showNetPrices();

                /** @var TemplateConfigService $templateConfigService */
                $templateConfigService = pluginApp(TemplateConfigService::class);

                $showAllShippingProfiles = $templateConfigService->getBoolean(
                    'checkout.show_all_shipping_profiles',
                    false
                );

                $webstoreId = Utils::getWebstoreId();
                $params = [
                    'countryId' => $this->checkout->getShippingCountryId(),
                    'webstoreId' => $webstoreId,
                    'skipCheckForMethodOfPaymentId' => $showAllShippingProfiles
                ];

                $deliveryAddressId = $this->getDeliveryAddressId();
                $type = AddressType::DELIVERY;

                if ($deliveryAddressId == 0 && $this->getBillingAddressId() > 0) {
                    $deliveryAddressId = $this->getBillingAddressId();
                    $type = AddressType::BILLING;
                }

                if ($deliveryAddressId > 0) {
                    try {
                        $address = $this->customerService->getAddress($deliveryAddressId, $type);
                        $params['zipCode'] = $address->postalCode;
                    } catch (\Exception $exception) {
                    }
                }


                $shippingProfilesList = $this->parcelServicePresetRepo->getLastWeightedPresetCombinations(
                    $this->basketRepository->load(),
                    $this->sessionStorageRepository->getCustomer()->accountContactClassId,
                    $params
                );

                $list = $this->filterShippingProfiles($shippingProfilesList);

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
                }

                $basket = $this->basketService->getBasket();
                if ($basket->currency !== $this->currencyExchangeRepo->getDefaultCurrency()) {
                    if (is_array($list)) {
                        foreach ($list as $key => $shippingProfile) {
                            if (isset($shippingProfile['shippingAmount'])) {
                                $list[$key]['shippingAmount'] = $this->currencyExchangeRepo->convertFromDefaultCurrency(
                                    $basket->currency,
                                    $list[$key]['shippingAmount']
                                );
                            }
                        }
                    }
                }

                return $list;
            }
        );
    }


    private function filterShippingProfiles($shippingProfilesList)
    {
        $paymentMethodList = $this->getCheckoutPaymentDataList();
        $list = [];
        foreach ($shippingProfilesList as $shippingProfile) {
            $shouldKeepShippingProfile = false;
            foreach ($paymentMethodList as $paymentMethod) {
                if (!in_array($paymentMethod['id'], $shippingProfile['excludedPaymentMethodIds'])) {
                    $shouldKeepShippingProfile = true;
                    $shippingProfile['allowedPaymentMethodNames'][] = $paymentMethod['name'];
                }
            }
            if ($shouldKeepShippingProfile) {
                $list[] = $shippingProfile;
            }
        }
        return $list;
    }

    /**
     * Get the ID of the current shipping country
     * @return int
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract::getShippingCountryId()
     */
    public function getShippingCountryId()
    {
        return $this->checkoutRepository->getShippingCountryId();
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

        if (is_null($billingAddressId) || (int)$billingAddressId <= 0) {
            $addresses = $this->customerService->getAddresses(AddressType::BILLING);
            $addresses = ArrayHelper::toArray($addresses);
            if (is_array($addresses) && count($addresses) > 0) {
                $billingAddressId = $addresses[0]['id'];
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
        $defaultShippingCountryId = $this->webstoreConfigurationRepository->getDefaultShippingCountryId();
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
        if ($this->getReadOnlyCheckout() !== $readonly) {
            /** @var Dispatcher $dispatcher */
            $dispatcher = pluginApp(Dispatcher::class);
            $dispatcher->fire(pluginApp(CheckoutReadonlyChanged::class, ['isReadonly' => $readonly]));
            $this->sessionStorageRepository->setSessionValue(
                SessionStorageRepositoryContract::READONLY_CHECKOUT,
                $readonly
            );
        }
    }

    public function getReadOnlyCheckout()
    {
        $readOnlyCheckout = $this->sessionStorageRepository->getSessionValue(
            SessionStorageRepositoryContract::READONLY_CHECKOUT
        );
        return (!is_null($readOnlyCheckout) ? $readOnlyCheckout : false);
    }
}
