<?php //strict

namespace LayoutCore\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;

use LayoutCore\Services\AvailabilityService;
use LayoutCore\Services\BasketService;
use LayoutCore\Services\CategoryService;
use LayoutCore\Services\CheckoutService;
use LayoutCore\Services\CountryService;
use LayoutCore\Services\CustomerService;
use LayoutCore\Services\ItemService;
use LayoutCore\Services\OrderService;
use LayoutCore\Services\SessionStorageService;
use LayoutCore\Services\UnitService;
use LayoutCore\Services\TemplateService;
use LayoutCore\Services\NotificationService;
use LayoutCore\Services\ContactBankService;
use LayoutCore\Services\WebstoreConfigurationService;
use LayoutCore\Services\LocalizationService;

/**
 * Provide services and helper functions to twig engine
 * Class TwigServiceProvider
 * @package LayoutCore\Extensions
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
        return "LayoutCore_Extension_TwigServiceProvider";
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
