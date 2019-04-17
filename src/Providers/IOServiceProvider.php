<?php // strict

namespace IO\Providers;

use IO\Constants\SessionStorageKeys;
use IO\Extensions\Basket\IOFrontendShippingProfileChanged;
use IO\Extensions\Basket\IOFrontendUpdateDeliveryAddress;
use IO\Extensions\ContentCache\IOAfterBuildPlugins;
use IO\Extensions\Facets\CategoryFacet;
use IO\Extensions\Mail\IOSendMail;
use IO\Extensions\Sitemap\IOSitemapPattern;
use IO\Extensions\TwigIOExtension;
use IO\Extensions\TwigServiceProvider;
use IO\Extensions\TwigTemplateContextExtension;
use IO\Middlewares\Middleware;
use IO\Services\AuthenticationService;
use IO\Services\AvailabilityService;
use IO\Services\BasketService;
use IO\Services\CategoryService;
use IO\Services\CheckoutService;
use IO\Services\ContactBankService;
use IO\Services\ContactMailService;
use IO\Services\CountryService;
use IO\Services\CouponService;
use IO\Services\CustomerPasswordResetService;
use IO\Services\CustomerService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\ItemService;
use IO\Services\ItemWishListService;
use IO\Services\LegalInformationService;
use IO\Services\LiveShoppingService;
use IO\Services\LocalizationService;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use IO\Services\OrderTotalsService;
use IO\Services\PriceDetectService;
use IO\Services\SalesPriceService;
use IO\Services\SessionStorageService;
use IO\Services\ShippingService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use IO\Services\UnitService;
use IO\Services\UrlService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Authentication\Events\AfterAccountAuthentication;
use Plenty\Modules\Authentication\Events\AfterAccountContactLogout;
use IO\Events\Basket\BeforeBasketItemToOrderItem;
use Plenty\Modules\Frontend\Events\FrontendCurrencyChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingProfileChanged;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Item\Stock\Hooks\CheckItemStock;
use Plenty\Modules\Order\Events\OrderCreated;
use Plenty\Modules\Plugin\Events\AfterBuildPlugins;
use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Events\Dispatcher;

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
        $this->addGlobalMiddleware(Middleware::class);
        $this->getApplication()->register(IORouteServiceProvider::class);

        $this->getApplication()->singleton('IO\Helper\TemplateContainer');

        $this->getApplication()->bind('IO\Builder\Item\ItemColumnBuilder');
        $this->getApplication()->bind('IO\Builder\Item\ItemFilterBuilder');
        $this->getApplication()->bind('IO\Builder\Item\ItemParamsBuilder');

        // Register services
        $this->registerSingletons([
            AuthenticationService::class,
            AvailabilityService::class,
            BasketService::class,
            CategoryService::class,
            CheckoutService::class,
            ContactBankService::class,
            ContactMailService::class,
            CountryService::class,
            CouponService::class,
            CustomerPasswordResetService::class,
            CustomerService::class,
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
            SessionStorageService::class,
            ShippingService::class,
            TemplateConfigService::class,
            TemplateService::class,
            UnitService::class,
            UrlService::class,
            WebstoreConfigurationService::class,
            LiveShoppingService::class
        ]);

        $this->getApplication()->singleton(FacetExtensionContainer::class);
    }

    /**
     * boot twig extensions and services
     * @param Twig $twig
     */
    public function boot(Twig $twig, Dispatcher $dispatcher)
    {
        $twig->addExtension(TwigServiceProvider::class);
        $twig->addExtension(TwigIOExtension::class);
        $twig->addExtension(TwigTemplateContextExtension::class);
        $twig->addExtension('Twig_Extensions_Extension_Intl');

        $dispatcher->listen(AfterAccountAuthentication::class, function($event)
        {
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);
            $customerService->resetGuestAddresses();
        });
    
        $dispatcher->listen(BeforeBasketItemToOrderItem::class, CheckItemStock::class);
        
        $dispatcher->listen(AfterAccountContactLogout::class, function($event)
        {
            /** @var CheckoutService $checkoutService */
            $checkoutService = pluginApp(CheckoutService::class);
            $checkoutService->setDefaultShippingCountryId();
        });
    
        $dispatcher->listen(OrderCreated::class, function($event)
        {
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);
            $customerService->resetGuestAddresses();
        
            /** @var BasketService $basketService */
            $basketService = pluginApp(BasketService::class);
            $basketService->resetBasket();
        });

        $dispatcher->listen(LoadSitemapPattern::class, IOSitemapPattern::class);
        $dispatcher->listen(PluginSendMail::class, IOSendMail::class);
        $dispatcher->listen(AfterBuildPlugins::class, IOAfterBuildPlugins::class);

        $dispatcher->listen('IO.initFacetExtensions', function (FacetExtensionContainer $facetExtensionContainer) {
            $facetExtensionContainer->addFacetExtension(pluginApp(CategoryFacet::class));
        });

        $dispatcher->listen(FrontendCurrencyChanged::class, function ($event) {
            $sessionStorage = pluginApp( FrontendSessionStorageFactoryContract::class );
            $sessionStorage->getPlugin()->setValue(SessionStorageKeys::CURRENCY, $event->getCurrency());
        });

        $dispatcher->listen(FrontendShippingProfileChanged::class, IOFrontendShippingProfileChanged::class);
        $dispatcher->listen(FrontendUpdateDeliveryAddress::class, IOFrontendUpdateDeliveryAddress::class);
    }

    private function registerSingletons( $classes )
    {
        foreach( $classes as $class )
        {
            $this->getApplication()->singleton( $class );
        }
    }
}
