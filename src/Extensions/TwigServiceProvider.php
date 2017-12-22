<?php //strict

namespace IO\Extensions;

use IO\Services\ContactMailService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemWishListService;
use IO\Services\OrderTotalsService;
use IO\Services\UrlService;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\AvailabilityService;
use IO\Services\BasketService;
use IO\Services\CategoryService;
use IO\Services\CheckoutService;
use IO\Services\CountryService;
use IO\Services\CustomerService;
use IO\Services\ItemService;
use IO\Services\OrderService;
use IO\Services\SessionStorageService;
use IO\Services\UnitService;
use IO\Services\TemplateService;
use IO\Services\NotificationService;
use IO\Services\ContactBankService;
use IO\Services\WebstoreConfigurationService;
use IO\Services\LocalizationService;
use IO\Services\CouponService;
use IO\Services\LegalInformationService;
use IO\Services\SalesPriceService;
use IO\Services\ItemLastSeenService;

/**
 * Provide services and helper functions to twig engine
 * Class TwigServiceProvider
 * @package IO\Extensions
 */
class TwigServiceProvider extends Twig_Extension
{
    public function __construct()
    {
        
    }

    /**
     * Return the name of the extension. The name must be unique.
     *
     * @return string The name of the extension
     */
    public function getName():string
    {
        return "IO_Extension_TwigServiceProvider";
    }

    /**
     * Return a list of filters to add.
     *
     * @return array The list of filters to add.
     */
    public function getFilters():array
    {
        return [];
    }

    /**
     * Return a list of functions to add.
     *
     * @return array the list of functions to add.
     */
    public function getFunctions():array
    {
        return [];
    }


    /**
     * Return a map of global helper objects to add.
     *
     * @return array the map of helper objects to add.
     */
    public function getGlobals():array
    {
        return [
            "services" => [
                "availability"      => pluginApp( AvailabilityService::class ),
                "basket"            => pluginApp( BasketService::class ),
                "category"          => pluginApp( CategoryService::class ),
                "checkout"          => pluginApp( CheckoutService::class ),
                "country"           => pluginApp( CountryService::class ),
                "customer"          => pluginApp( CustomerService::class ),
                "item"              => pluginApp( ItemService::class ),
                "itemLoader"        => pluginApp( ItemLoaderService::class ),
                "order"             => pluginApp( OrderService::class ),
                "sessionStorage"    => pluginApp( SessionStorageService::class ),
                "unit"              => pluginApp( UnitService::class),
                "contactBank"       => pluginApp( ContactBankService::class ),
                "template"          => pluginApp( TemplateService::class),
                "notifications"     => pluginApp( NotificationService::class ),
                "webstoreConfig"    => pluginApp( WebstoreConfigurationService::class),
                "localization"      => pluginApp( LocalizationService::class ),
                "coupon"            => pluginApp( CouponService::class ),
                "legalInformation"  => pluginApp( LegalInformationService::class ),
                "salesPrice"        => pluginApp( SalesPriceService::class ),
                "lastSeen"          => pluginApp( ItemLastSeenService::class ),
                "crossSelling"      => pluginApp( ItemCrossSellingService::class ),
                "wishList"          => pluginApp( ItemWishListService::class ),
                "contactMail"       => pluginApp( ContactMailService::class),
                "totalsService"     => pluginApp( OrderTotalsService::class),
                "url"               => pluginApp( UrlService::class )
            ]
        ];
    }
}
