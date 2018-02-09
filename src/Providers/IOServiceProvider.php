<?php // strict

namespace IO\Providers;

use IO\Api\Resources\CouponResource;
use IO\Extensions\TwigIOExtension;
use IO\Extensions\TwigServiceProvider;
use IO\Middlewares\Middleware;
use IO\Services\AuthenticationService;
use IO\Services\AvailabilityService;
use IO\Services\BasketService;
use IO\Services\CategoryService;
use IO\Services\CheckoutService;
use IO\Services\ContactBankService;
use IO\Services\ContactMailService;
use IO\Services\ContentCaching\ContentCachingProvider;
use IO\Services\CountryService;
use IO\Services\CouponService;
use IO\Services\CustomerPasswordResetService;
use IO\Services\CustomerService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use IO\Services\ItemLoader\Factories\ItemLoaderFactoryES;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\ItemService;
use IO\Services\ItemWishListService;
use IO\Services\LegalInformationService;
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
        $this->getApplication()->register(ContentCachingProvider::class);
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
            WebstoreConfigurationService::class
        ]);
        
        //TODO check ES ready state
        $this->getApplication()->bind(ItemLoaderFactory::class, ItemLoaderFactoryES::class);
        $this->getApplication()->singleton(FacetExtensionContainer::class);
    }

    /**
     * boot twig extensions and services
     * @param Twig $twig
     */
    public function boot(Twig $twig)
    {
        $twig->addExtension(TwigServiceProvider::class);
        $twig->addExtension(TwigIOExtension::class);
        $twig->addExtension('Twig_Extensions_Extension_Intl');
        $twig->addExtension(TwigLoaderPresets::class);
    }

    private function registerSingletons( $classes )
    {
        foreach( $classes as $class )
        {
            $this->getApplication()->singleton( $class );
        }
    }
}
