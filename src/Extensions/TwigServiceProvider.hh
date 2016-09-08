<?hh //strict

namespace LayoutCore\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Extensions\Twig_SimpleFunction;
use Plenty\Plugin\Templates\Extensions\Twig_SimpleFilter;
use LayoutCore\Helper\AbstractFactory;
/**
 * Provide services and helper functions to twig engine
 */
class TwigServiceProvider extends Twig_Extension
{
    private AbstractFactory $factory;

    public function __construct( AbstractFactory $factory )
    {
        $this->factory  = $factory;
    }

    /**
     * Returns the name of the extension. Must be unique.
     *
     * @return string The name of the extension
     */
    public function getName():string
    {
        return "LayoutCore_Extension_TwigServiceProvider";
    }

    /**
     * Returns a list of filters to add.
     *
     * @return array<Twig_SimpleFilter> The list of filters to add.
     */
    public function getFilters():array<Twig_SimpleFilter>
    {
        return [];
    }

    /**
     * Returns a list of functions to add.
     *
     * @return array<Twig_SimpleFunction> the list of functions to add.
     */
    public function getFunctions():array<Twig_SimpleFunction>
    {
        return [];
    }

    /**
     * Returns a map of global helper objects to add.
     *
     * @return array<string, mixed> the map of helper objects to add.
     */
    public function getGlobals():array<string, mixed>
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
                "navigation"    => $this->factory->make( \LayoutCore\Services\NavigationService::class ),
                "order"         => $this->factory->make( \LayoutCore\Services\OrderService::class ),
                "sessionStorage"=> $this->factory->make( \LayoutCore\Services\SessionStorageService::class ),
                "unit"          => $this->factory->make( \LayoutCore\Services\UnitService::class)
            ]
        ];
    }
}
