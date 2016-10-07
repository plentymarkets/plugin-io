<?hh //strict

namespace LayoutCore\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Extensions\Twig_SimpleFunction;
use Plenty\Plugin\Templates\Extensions\Twig_SimpleFilter;
use Plenty\Plugin\Templates\Factories\TwigFactory;

use Plenty\Plugin\Http\Request;

use LayoutCore\Extensions\AbstractFilter;
use LayoutCore\Extensions\AbstractFunction;
use LayoutCore\Extensions\Filters\PatternFilter;
use LayoutCore\Extensions\Filters\NumberFormatFilter;
use LayoutCore\Extensions\Filters\URLFilter;
use LayoutCore\Extensions\Filters\ItemNameFilter;

use LayoutCore\Extensions\Functions\GetBasePrice;
use LayoutCore\Extensions\Functions\Component;

/**
 * Provide services and helper functions to twig engine
 */
class TwigLayoutCoreExtension extends Twig_Extension
{
    private TwigFactory $twig;
    private Request $request;

    public function __construct(
        TwigFactory $twig,
        Request $request,
        PatternFilter $patternFilter,
        NumberFormatFilter $numberFormatFilter,
        URLFilter $urlFilter,
        GetBasePrice $getBasePrice,
        Component $component,
        ItemNameFilter $itemNameFilter
    )
    {
        $this->twig = $twig;
        $this->request = $request;
    }

    /**
     * Returns the name of the extension. Must be unique.
     *
     * @return string The name of the extension
     */
    public function getName():string
    {
        return "LayoutCore_Extension_TwigLayoutCoreExtensions";
    }

    /**
     * Returns a list of filters to add.
     *
     * @return array<Twig_SimpleFilter> The list of filters to add.
     */
    public function getFilters():array<Twig_SimpleFilter>
    {
        $filters = array();
        foreach( AbstractFilter::$filters as $abstractFilter )
        {
            foreach( $abstractFilter->getFilters() as $filterName => $callable )
            {
                array_push(
                    $filters,
                    $this->twig->createSimpleFilter( $filterName, [$abstractFilter, $callable] )
                );
            }
        }
        return $filters;
    }

    /**
     * Returns a list of functions to add.
     *
     * @return array<Twig_SimpleFunction> the list of functions to add.
     */
    public function getFunctions():array<Twig_SimpleFunction>
    {
        $functions = array();
        foreach( AbstractFunction::$functions as $abstractFunction )
        {
            foreach( $abstractFunction->getFunctions() as $functionName => $callable )
            {
                array_push(
                    $functions,
                    $this->twig->createSimpleFunction( $functionName, [$abstractFunction, $callable] )
                );
            }
        }
        return $functions;
    }

    public function getGlobals():array<string, mixed>
    {
        return [
            "request" => $this->request
        ];
    }
}
