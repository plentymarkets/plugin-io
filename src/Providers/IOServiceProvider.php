<?php

namespace IO\Providers;

use IO\Config\IOConfig;
use IO\Events\Basket\BeforeBasketItemToOrderItem;
use IO\Extensions\Basket\IOFrontendShippingProfileChanged;
use IO\Extensions\Basket\IOFrontendUpdateDeliveryAddress;
use IO\Extensions\ContentCache\IOAfterBuildPlugins;
use IO\Extensions\Facets\CategoryFacet;
use IO\Extensions\Mail\IOSendMail;
use IO\Extensions\Sitemap\IOSitemapPattern;
use IO\Extensions\TwigIOExtension;
use IO\Extensions\TwigServiceProvider;
use IO\Extensions\TwigTemplateContextExtension;
use IO\Jobs\CleanupUserDataHashes;
use IO\Middlewares\AuthenticateWithToken;
use IO\Middlewares\CheckNotFound;
use IO\Middlewares\ClearNotifications;
use IO\Middlewares\DetectCurrency;
use IO\Middlewares\DetectLanguage;
use IO\Middlewares\DetectLegacySearch;
use IO\Middlewares\DetectReadonlyCheckout;
use IO\Middlewares\DetectReferrer;
use IO\Middlewares\DetectShippingCountry;
use IO\Middlewares\HandleNewsletter;
use IO\Middlewares\HandleOrderPreviewUrl;
use IO\Services\AuthenticationService;
use IO\Services\AvailabilityService;
use IO\Services\BasketService;
use IO\Services\CategoryService;
use IO\Services\CheckoutService;
use IO\Services\ContactBankService;
use IO\Services\ContactMailService;
use IO\Services\ContactMapService;
use IO\Services\CountryService;
use IO\Services\CouponService;
use IO\Services\CustomerService;
use IO\Services\FacetService;
use IO\Services\FakerService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemService;
use IO\Services\ItemWishListService;
use IO\Services\LegalInformationService;
use IO\Services\LiveShoppingService;
use IO\Services\LocalizationService;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use IO\Services\OrderTotalsService;
use IO\Services\PriceDetectService;
use IO\Services\PropertyFileService;
use IO\Services\SalesPriceService;
use IO\Services\ShippingService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use IO\Services\UnitService;
use IO\Services\UrlService;
use Plenty\Modules\Authentication\Events\AfterAccountAuthentication;
use Plenty\Modules\Authentication\Events\AfterAccountContactLogout;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Modules\Frontend\Events\FrontendCurrencyChanged;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingProfileChanged;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;
use Plenty\Modules\Frontend\Session\Events\AfterSessionCreate;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Item\ItemCoupon\Hooks\CheckItemRestriction;
use Plenty\Modules\Item\Stock\Hooks\CheckItemStock;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Plugin\Events\AfterBuildPlugins;
use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\FacetExtensionContainer;
use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;

/**
 * Class IOServiceProvider
 * @package IO\Providers
 */
class IOServiceProvider extends ServiceProvider
{
    /**
     * Register the core functions
     */
    public function register()
    {
        $this->registerMiddlewares(
            [
                AuthenticateWithToken::class,
                DetectCurrency::class,
                DetectLanguage::class,
                DetectLegacySearch::class,
                DetectReadonlyCheckout::class,
                DetectReferrer::class,
                DetectShippingCountry::class,
                HandleNewsletter::class,
                HandleOrderPreviewUrl::class,
                ClearNotifications::class,
                CheckNotFound::class
            ]
        );
        $this->getApplication()->register(IORouteServiceProvider::class);

        $this->getApplication()->singleton('IO\Helper\TemplateContainer');

        $this->getApplication()->bind('IO\Builder\Item\ItemColumnBuilder');
        $this->getApplication()->bind('IO\Builder\Item\ItemFilterBuilder');
        $this->getApplication()->bind('IO\Builder\Item\ItemParamsBuilder');

        // Register services
        $this->registerSingletons(
            [
                AuthenticationService::class,
                AvailabilityService::class,
                BasketService::class,
                CategoryService::class,
                CheckoutService::class,
                ContactBankService::class,
                ContactMailService::class,
                CountryService::class,
                CouponService::class,
                CustomerService::class,
                FacetService::class,
                ItemCrossSellingService::class,
                ItemLastSeenService::class,
                ItemService::class,
                ItemWishListService::class,
                LegalInformationService::class,
                LocalizationService::class,
                NotificationService::class,
                OrderService::class,
                OrderTotalsService::class,
                PriceDetectService::class,
                SalesPriceService::class,
                ShippingService::class,
                TemplateConfigService::class,
                TemplateService::class,
                UnitService::class,
                UrlService::class,
                LiveShoppingService::class,
                FakerService::class,
                PropertyFileService::class,
                ContactMapService::class
            ]
        );
    }

    /**
     * boot twig extensions and services
     * @param Twig $twig
     */
    public function boot(Twig $twig, Dispatcher $dispatcher, CronContainer $cronContainer)
    {
        $this->registerConfigValues();

        $twig->addExtension(TwigServiceProvider::class);
        $twig->addExtension(TwigIOExtension::class);
        $twig->addExtension(TwigTemplateContextExtension::class);
        $twig->addExtension('Twig_Extensions_Extension_Intl');

        $dispatcher->listen(
            AfterAccountAuthentication::class,
            function ($event) {
                /** @var CustomerService $customerService */
                $customerService = pluginApp(CustomerService::class);
                $customerService->resetGuestAddresses();

                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                //validate methodOfPayment
                $methodOfPaymentId = $checkoutService->getMethodOfPaymentId();
            }
        );

        /**
         * @deprecated since 5.0.0 will be removed in 6.0.0
         * @see \Plenty\Modules\Webshop\Events\BeforeBasketItemToOrderItem
         */
        $dispatcher->listen(BeforeBasketItemToOrderItem::class, CheckItemStock::class);
        /**
         * @deprecated since 5.0.0 will be removed in 6.0.0
         * @see \Plenty\Modules\Webshop\Events\BeforeBasketItemToOrderItem
         */
        $dispatcher->listen(BeforeBasketItemToOrderItem::class, CheckItemRestriction::class);

        $dispatcher->listen(
            AfterAccountContactLogout::class,
            function ($event) {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setDefaultShippingCountryId();
                //validate methodOfPayment
                $methodOfPaymentId = $checkoutService->getMethodOfPaymentId();

                /** @var SessionStorageRepositoryContract $sessionStorageRepostory */
                $sessionStorageRepostory = pluginApp(SessionStorageRepositoryContract::class);
                $sessionStorageRepostory->setSessionValue(SessionStorageRepositoryContract::LAST_ACCESSED_ORDER, null);
                $sessionStorageRepostory->setSessionValue(SessionStorageRepositoryContract::LATEST_ORDER_ID, null);
            }
        );

        $dispatcher->listen(
            AfterSessionCreate::class,
            function ($event) {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                //validate methodOfPayment
                $methodOfPaymentId = $checkoutService->getMethodOfPaymentId();
            }
        );


        $dispatcher->listen(
            AfterBasketChanged::class,
            function ($event) {
                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp(CheckoutService::class);
                $checkoutService->setReadOnlyCheckout(false);
            }
        );

        $dispatcher->listen(
            ExecutePayment::class,
            function ($event) {
                /** @var CustomerService $customerService */
                $customerService = pluginApp(CustomerService::class);
                $customerService->resetGuestAddresses();

                /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);
                $basketService->resetBasket();
            }
        );

        $dispatcher->listen(LoadSitemapPattern::class, IOSitemapPattern::class);
        $dispatcher->listen(PluginSendMail::class, IOSendMail::class);
        $dispatcher->listen(AfterBuildPlugins::class, IOAfterBuildPlugins::class);

        $facetExtensionContainer = pluginApp(FacetExtensionContainer::class);
        $facetExtensionContainer->addFacetExtension(pluginApp(CategoryFacet::class));


        $dispatcher->listen(
            FrontendCurrencyChanged::class,
            function ($event) {
                $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);
                $sessionStorage->getPlugin()->setValue(SessionStorageRepositoryContract::CURRENCY, $event->getCurrency());
                 /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);
                $basketService->checkBasketItemsCurrency();
            }
        );

        $dispatcher->listen(
            FrontendLanguageChanged::class,
            function ($event) {
                /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);
                $basketService->checkBasketItemsLang($event->getLanguage());
                DetectLanguage::$DETECTED_LANGUAGE = $event->getLanguage();
            }
        );

        $dispatcher->listen(FrontendShippingProfileChanged::class, IOFrontendShippingProfileChanged::class);
        $dispatcher->listen(FrontendUpdateDeliveryAddress::class, IOFrontendUpdateDeliveryAddress::class);

        $cronContainer->add(CronContainer::DAILY, CleanupUserDataHashes::class);
    }

    private function registerSingletons($classes)
    {
        foreach ($classes as $class) {
            $this->getApplication()->singleton($class);
        }
    }

    private function registerMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->addGlobalMiddleware($middleware);
        }
    }

    private function registerConfigValues()
    {
        /** @var IOConfig $ioConfig */
        $ioConfig = pluginApp(IOConfig::class);

        /** @var TemplateConfigRepositoryContract $templateConfigRepo */
        $templateConfigRepo = pluginApp(TemplateConfigRepositoryContract::class);

        $templateConfigRepo
            ->registerConfigValue('format.number_decimals', $ioConfig->format->numberDecimals)
            ->registerConfigValue('format.separator_decimal', $ioConfig->format->separatorDecimal)
            ->registerConfigValue('format.separator_thousands', $ioConfig->format->separatorThousands)
            ->registerConfigValue('format.use_locale_currency_format', $ioConfig->format->useLocaleCurrencyFormat);
    }
}
