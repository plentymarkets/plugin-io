<?php //strict

namespace IO\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;

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
                "availability"  => pluginApp( AvailabilityService::class ),
                "basket"        => pluginApp( BasketService::class ),
                "category"      => pluginApp( CategoryService::class ),
                "checkout"      => pluginApp( CheckoutService::class ),
                "country"       => pluginApp( CountryService::class ),
                "customer"      => pluginApp( CustomerService::class ),
                "item"          => pluginApp( ItemService::class ),
                "order"         => pluginApp( OrderService::class ),
                "sessionStorage"=> pluginApp( SessionStorageService::class ),
                "unit"          => pluginApp( UnitService::class),
                "template"      => pluginApp( TemplateService::class),
                "notifications" => pluginApp( NotificationService::class ),
                "contactBank"   => pluginApp( ContactBankService::class),
                "webstoreConfig"=> pluginApp( WebstoreConfigurationService::class),
                "localization"  => pluginApp( LocalizationService::class )
            ]
        ];
    }
}
