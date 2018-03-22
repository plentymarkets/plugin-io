<?php //strict

namespace IO\Services;

use IO\Builder\Order\AddressType;
use IO\Helper\LanguageMap;
use IO\Helper\MemoryCache;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Frontend\Events\ValidateCheckoutEvent;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Order\Currency\Contracts\CurrencyRepositoryContract;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use IO\Constants\SessionStorageKeys;
use IO\Services\BasketService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Application;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Translation\Translator;

/**
 * Class CheckoutService
 * @package IO\Services
 */
class CheckoutService
{
    use MemoryCache;

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
     * CheckoutService constructor.
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
     * @param Checkout $checkout
     * @param BasketRepositoryContract $basketRepository
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param CustomerService $customerService
     */
    public function __construct(
        FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository,
        Checkout $checkout,
        BasketRepositoryContract $basketRepository,
        FrontendSessionStorageFactoryContract $sessionStorage,
        CustomerService $customerService)
    {
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
        $this->checkout                        = $checkout;
        $this->basketRepository                = $basketRepository;
        $this->sessionStorage                  = $sessionStorage;
        $this->customerService                 = $customerService;
    }

    /**
     * Get the relevant data for the checkout
     * @return array
     */
    public function getCheckout(): array
    {
        return [
            "currency" => $this->getCurrency(),
            "currencyList" => $this->getCurrencyList(),
            "methodOfPaymentId" => $this->getMethodOfPaymentId(),
            "methodOfPaymentList" => $this->getMethodOfPaymentList(),
            "shippingCountryId" => $this->getShippingCountryId(),
            "shippingProfileId" => $this->getShippingProfileId(),
            "shippingProfileList" => $this->getShippingProfileList(),
            "deliveryAddressId" => $this->getDeliveryAddressId(),
            "billingAddressId" => $this->getBillingAddressId(),
            "paymentDataList" => $this->getCheckoutPaymentDataList(),
        ];
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
        }

        return [
            "separator_decimal" => $formatter->getSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL),
            "separator_thousands" => $formatter->getSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL),
            "pattern" => $formatter->getPattern()
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
                foreach ($validateCheckoutEvent->getErrorKeysList() as $errorKey) {
                    $errors[] = $translator->trans($errorKey);
                }

                return array(
                    "type" => GetPaymentMethodContent::RETURN_TYPE_ERROR,
                    "value" => implode('<br>', $errors)
                );
            }
        }

        $mopId = $this->getMethodOfPaymentId();
        return pluginApp(PaymentMethodRepositoryContract::class)->preparePaymentMethod($mopId);
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
        $lang            = pluginApp(SessionStorageService::class)->getLang();
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
        /** @var SessionStorageService $sessionService */
        $sessionService = pluginApp(SessionStorageService::class);
        $showNetPrice   = $sessionService->getCustomer()->showNetPrice;

        /** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepo */
        $parcelServicePresetRepo = pluginApp(ParcelServicePresetRepositoryContract::class);

        $contact = $this->customerService->getContact();
        $params  = [
            'countryId'  => $this->getShippingCountryId(),
            'webstoreId' => pluginApp(Application::class)->getWebstoreId(),
        ];
        $list    = $parcelServicePresetRepo->getLastWeightedPresetCombinations($this->basketRepository->load(), $contact->classId, $params);

        if ($showNetPrice) {
            /** @var BasketService $basketService */
            $basketService = pluginApp(BasketService::class);
            $maxVatValue   = $basketService->getMaxVatValue();

            if (is_array($list)) {
                foreach ($list as $key => $shippingProfile) {
                    if (isset($shippingProfile['shippingAmount'])) {
                        $list[$key]['shippingAmount'] = (100.0 * $shippingProfile['shippingAmount']) / (100.0 + $maxVatValue);
                    }
                }
            }
        }

        return $list;
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
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        return (int)$basketService->getDeliveryAddressId();
    }

    /**
     * Set the ID of the current delivery address
     * @param int $deliveryAddressId
     */
    public function setDeliveryAddressId($deliveryAddressId)
    {
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        $basketService->setDeliveryAddressId($deliveryAddressId);
    }

    /**
     * Get the ID of the current invoice address
     * @return int
     */
    public function getBillingAddressId()
    {
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);

        $billingAddressId = $basketService->getBillingAddressId();

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
            /**
             * @var BasketService $basketService
             */
            $basketService = pluginApp(BasketService::class);
            $basketService->setBillingAddressId($billingAddressId);
        }
    }
    
    public function setDefaultShippingCountryId()
    {
        /** @var WebstoreConfigurationService $webstoreConfig */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        $defaultShippingCountryId = $webstoreConfigService->getDefaultShippingCountryId();
        
        $this->setShippingCountryId($defaultShippingCountryId);
    }
}