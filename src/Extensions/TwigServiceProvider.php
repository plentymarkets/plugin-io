<?php //strict

namespace IO\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;

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
            "services" => pluginApp( TwigServiceContainer::class )
        ];
    }
}
