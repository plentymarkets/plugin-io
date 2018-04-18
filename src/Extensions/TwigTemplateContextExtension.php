<?php //strict

namespace IO\Extensions;

use IO\Extensions\Filters\ItemImagesFilter;
use IO\Extensions\Functions\QueryString;
use IO\Extensions\Functions\UniqueId;
use IO\Helper\ArrayHelper;
use IO\Helper\ContextInterface;
use IO\Helper\TemplateContainer;
use IO\Services\TemplateService;
use Plenty\Plugin\Events\Dispatcher;
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

class TwigTemplateContextExtension extends Twig_Extension
{

    /**
     * Return the name of the extension. The name must be unique.
     *
     * @return string The name of the extension
     */
    public function getName():string
    {
        return "IO_Extension_TwigTemplateContextExtensions";
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
     * Return a list of global variables
     * @return array
     */
    public function getGlobals():array
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp(Dispatcher::class);
        $contextEvent = 'ctx.default';
        if ( strlen(TemplateService::$currentTemplate) )
        {
            $contextEvent = 'ctx' . substr(TemplateService::$currentTemplate, 3);
        }

        /** @var TemplateContainer $templateContainer */
        $templateContainer = pluginApp(TemplateContainer::class);
        $dispatcher->fire('IO.' . $contextEvent, [$templateContainer]);

        $contextClass = $templateContainer->getContext();
        if(strlen($contextClass))
        {
            $context = pluginApp( $contextClass );
            if ( $context instanceof ContextInterface )
            {
                $context->init( TemplateService::$currentTemplateData );
            }
            
            return ArrayHelper::toArray( $context );
        }
        
        return [];
    }
}
