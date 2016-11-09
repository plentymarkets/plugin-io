<?php //strict

namespace LayoutCore\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Extensions\Twig_SimpleFunction;
use Plenty\Plugin\Templates\Extensions\Twig_SimpleFilter;
use LayoutCore\Helper\AbstractFactory;

/**
 * Provide services and helper functions to twig engine
 * Class TwigServiceProvider
 * @package LayoutCore\Extensions
 */
class TwigServiceProvider extends Twig_Extension
{
    /**
    * @var AbstractFactory
    */
  private $factory;

    public function __construct( AbstractFactory $factory )
    {
        $this->factory  = $factory;
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
                "availability"  => $this->factory->make( \LayoutCore\Services\AvailabilityService::class ),
                "basket"        => $this->factory->make( \LayoutCore\Services\BasketService::class ),
                "category"      => $this->factory->make( \LayoutCore\Services\CategoryService::class ),
                "checkout"      => $this->factory->make( \LayoutCore\Services\CheckoutService::class ),
                "country"       => $this->factory->make( \LayoutCore\Services\CountryService::class ),
                "customer"      => $this->factory->make( \LayoutCore\Services\CustomerService::class ),
                "item"          => $this->factory->make( \LayoutCore\Services\ItemService::class ),
                "order"         => $this->factory->make( \LayoutCore\Services\OrderService::class ),
                "sessionStorage"=> $this->factory->make( \LayoutCore\Services\SessionStorageService::class ),
                "unit"          => $this->factory->make( \LayoutCore\Services\UnitService::class),
                "template"      => $this->factory->make( \LayoutCore\Services\TemplateService::class),
                "notifications" => $this->factory->make( \LayoutCore\Services\NotificationService::class ),
                "contactBank"   => $this->factory->make( \LayoutCore\Services\ContactBankService::class),
                "webstoreConfig"=> $this->factory->make( \LayoutCore\Services\WebstoreConfigurationService::class)
            ]
        ];
    }
}
