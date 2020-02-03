<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use IO\Helper\ComponentContainer;
use IO\Helper\EventDispatcher;
use IO\Helper\OverrideComponents;

/**
 * Class Component
 * @package IO\Extensions\Functions
 */
class Component extends AbstractFunction
{
    /**
     * @var array
     */
    private $components = array();

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "component" => "component",
            "has_component_template" => "hasComponentTemplate",
            "get_component_template" => "getComponentTemplate",
            "override_component" => "overrideComponent",
            "get_overridden_components" => "getOverriddenComponents"
        ];
    }

    /**
     * Push the component to the component stack
     * @param string $path
     */
    public function component( string $path )
    {
        if( !array_key_exists( $path, $this->components ) )
        {
            /** @var ComponentContainer $componentContainer */
            $componentContainer = pluginApp(ComponentContainer::class, ['originComponentTemplate' => $path]);

            EventDispatcher::fire('Component.Import', [
               $componentContainer
            ]);
            
            $this->components[$path] = empty($componentContainer->getNewComponentTemplate()) ? $componentContainer->getOriginComponentTemplate() : $componentContainer->getNewComponentTemplate();
        }
    }

    /**
     * Check whether a component template exists
     * @return bool
     */
    public function hasComponentTemplate():bool
    {
        return !empty($this->components);
    }

    /**
     * Get the component from the component stack
     * @return string
     */
    public function getComponentTemplate():string
    {
        return array_shift($this->components);
    }

    /**
     * @param string $componentTag
     * @param string $templateId
     */
    public function overrideComponent(string $componentTag, string $templateId)
    {
        OverrideComponents::overrideComponent($componentTag, $templateId);
    }

    /**
     * 
     */
    public function getOverriddenComponents():array
    {
        return OverrideComponents::getOverriddenComponents();
    }
}
