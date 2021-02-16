<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use IO\Helper\EventDispatcher;
use IO\Helper\ResourceContainer;

/**
 * Class AdditionalResources
 *
 * Contains twig functions that get additional styles or scripts.
 *
 * @package IO\Extensions\Functions
 */
class AdditionalResources extends AbstractFunction
{
    /**
     * @var ResourceContainer
     */
    private $resourceContainer = null;

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "get_additional_scripts" => "getAdditionalScripts",
            "get_additional_styles" => "getAdditionalStyles"
        ];
    }

    /**
     * Gets the additional scripts that are added on the 'Resource.Import' event.
     *
     * @return array
     */
    public function getAdditionalScripts()
    {
        /** @var ResourceContainer $resourceContainer */
        if ($this->resourceContainer === null) {
            $this->resourceContainer = pluginApp(ResourceContainer::class);
            EventDispatcher::fire(
                'Resources.Import',
                [
                    $this->resourceContainer
                ]
            );
        }

        return $this->resourceContainer->getScriptTemplates();
    }

    /**
     * Gets the additional styles that are added on the 'Resource.Import' event.
     *
     * @return array
     */
    public function getAdditionalStyles()
    {
        /** @var ResourceContainer $resourceContainer */
        if ($this->resourceContainer === null) {
            $this->resourceContainer = pluginApp(ResourceContainer::class);
            EventDispatcher::fire(
                'Resources.Import',
                [
                    $this->resourceContainer
                ]
            );
        }

        return $this->resourceContainer->getStyleTemplates();
    }
}