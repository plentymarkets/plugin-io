<?php //strict

namespace IO\Extensions;

use IO\Extensions\Filters\ItemImagesFilter;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Factories\TwigFactory;
use Plenty\Plugin\Http\Request;

use IO\Extensions\Filters\PatternFilter;
use IO\Extensions\Filters\NumberFormatFilter;
use IO\Extensions\Filters\OrderByKeyFilter;
use IO\Extensions\Filters\URLFilter;
use IO\Extensions\Filters\ItemNameFilter;
use IO\Extensions\Filters\ShuffleFilter;

use IO\Extensions\Functions\GetBasePrice;
use IO\Extensions\Functions\Component;
use IO\Extensions\Functions\ExternalContent;
use IO\Extensions\Functions\Partial;
use IO\Extensions\Functions\AdditionalResources;

/**
 * Provide services and helper functions to twig engine
 * Class TwigIOExtension
 * @package IO\Extensions
 */
class TwigIOExtension extends Twig_Extension
{
	/**
	 * @var TwigFactory
	 */
	private $twig;

    /**
     * @var Request
     */
    private $request;

	public function __construct(
		TwigFactory $twig,
        Request $request,
		PatternFilter $patternFilter,
		NumberFormatFilter $numberFormatFilter,
		URLFilter $urlFilter,
		GetBasePrice $getBasePrice,
		Component $component,
        AdditionalResources $additionalResources,
        ItemNameFilter $itemNameFilter,
        ExternalContent $externalContent,
        Partial $partial,
        ItemImagesFilter $itemImagesFilter,
		OrderByKeyFilter $orderByKeyFilter,
		ShuffleFilter $shuffleFilter
	)
	{
		$this->twig = $twig;
        $this->request = $request;
	}

	/**
	 * Return the name of the extension. The name must be unique.
	 *
	 * @return string The name of the extension
	 */
	public function getName():string
	{
		return "IO_Extension_TwigIOExtensions";
	}

	/**
	 * Return a list of filters to add.
	 *
	 * @return array The list of filters to add.
	 */
	public function getFilters():array
	{
		$filters = [];
		foreach(AbstractFilter::$filters as $abstractFilter)
		{
			foreach($abstractFilter->getFilters() as $filterName => $callable)
			{
				array_push(
					$filters,
					$this->twig->createSimpleFilter($filterName, [$abstractFilter, $callable])
				);
			}
		}
		return $filters;
	}

	/**
	 * Return a list of functions to add.
	 *
	 * @return array the list of functions to add.
	 */
	public function getFunctions():array
	{
		$functions = [];
		foreach(AbstractFunction::$functions as $abstractFunction)
		{
			foreach($abstractFunction->getFunctions() as $functionName => $callable)
			{
				array_push(
					$functions,
					$this->twig->createSimpleFunction($functionName, [$abstractFunction, $callable])
				);
			}
		}
		return $functions;
	}

    /**
     * Return a list of global variables
     * @return array
     */
    public function getGlobals():array
    {
        return [
            "request" => $this->request
        ];
    }
}
