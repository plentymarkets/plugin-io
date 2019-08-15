<?php // strict

namespace IO\Providers;

use IO\Constants\SessionStorageKeys;
use IO\Contracts\FacetSearchFactoryContract;
use IO\Contracts\MultiSearchFactoryContract;
use IO\Contracts\SortingContract;
use IO\Contracts\VariationSearchFactoryContract;
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
use IO\Services\CustomerService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemSearch\Factories\FacetSearchFactory;
use IO\Services\ItemSearch\Factories\MultiSearchFactory;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\FacetExtensionContainer;
use IO\Services\ItemSearch\Helper\SortingHelper;
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
use IO\Services\VdiSearch\Factories\FacetSearchFactory as FacetSearchFactoryVdi;
use IO\Services\VdiSearch\Factories\MultiSearchFactory as MultiSearchFactoryVdi;
use IO\Services\VdiSearch\Factories\VariationSearchFactory as VariationSearchFactoryVdi;
use IO\Services\VdiSearch\Helper\SortingHelper as SortingHelperVdi;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Authentication\Events\AfterAccountAuthentication;
use Plenty\Modules\Authentication\Events\AfterAccountContactLogout;
use IO\Events\Basket\BeforeBasketItemToOrderItem;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Modules\Frontend\Events\FrontendCurrencyChanged;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingProfileChanged;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Item\Stock\Hooks\CheckItemStock;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Plugin\Events\AfterBuildPlugins;
use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Plugin\ConfigRepository;
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
        $this->getApplication()->singleton('IO\Contracts\SortingContract');

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
    
        $this->bindItemSearchClasses();
    }

    /**
     * boot twig extensions and services
     * @param Twig $twig
     */
    public function boot(Twig $twig, Dispatcher $dispatcher, CronContainer $cronContainer)
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
    
        $dispatcher->listen(AfterBasketChanged::class, function($event)
        {
            /** @var CheckoutService $checkoutService */
            $checkoutService = pluginApp(CheckoutService::class);
            $checkoutService->setReadOnlyCheckout(false);
        });
    
        $dispatcher->listen(ExecutePayment::class, function($event)
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
        
        $dispatcher->listen(FrontendLanguageChanged::class, function($event) {
            /** @var BasketService $basketService */
            $basketService = pluginApp(BasketService::class);
            $basketService->checkBasketItemsLang();
            Middleware::$DETECTED_LANGUAGE = $event->getLanguage();
        });

        $dispatcher->listen(FrontendShippingProfileChanged::class, IOFrontendShippingProfileChanged::class);
        $dispatcher->listen(FrontendUpdateDeliveryAddress::class, IOFrontendUpdateDeliveryAddress::class);

        $cronContainer->add(CronContainer::DAILY, CleanupUserDataHashes::class );
    }

    private function registerSingletons( $classes )
    {
        foreach( $classes as $class )
        {
            $this->getApplication()->singleton( $class );
        }
    }
    
    private function bindItemSearchClasses()
    {
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        $vdiSearchActive = $config->get('IO.item_search.vdi_active');
    
        if($vdiSearchActive == 'true')
        {
            $this->getApplication()->bind(VariationSearchFactoryContract::class, VariationSearchFactoryVdi::class);
            $this->getApplication()->bind(MultiSearchFactoryContract::class, MultiSearchFactoryVdi::class);
            $this->getApplication()->bind(FacetSearchFactoryContract::class, FacetSearchFactoryVdi::class);
            $this->getApplication()->bind(SortingContract::class, SortingHelperVdi::class);
        }
        else
        {
            $this->getApplication()->bind(VariationSearchFactoryContract::class, VariationSearchFactory::class);
            $this->getApplication()->bind(MultiSearchFactoryContract::class, MultiSearchFactory::class);
            $this->getApplication()->bind(FacetSearchFactoryContract::class, FacetSearchFactory::class);
            $this->getApplication()->bind(SortingContract::class, SortingHelper::class);
        }
    }
}
