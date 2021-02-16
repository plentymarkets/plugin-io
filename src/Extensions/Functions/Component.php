<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use IO\Helper\ComponentContainer;
use IO\Helper\EventDispatcher;

/**
 * Class Component
 *
 * @package IO\Extensions\Functions
 */
class Component extends AbstractFunction
{
    /**
     * @var array
     */
    private $components = array();

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "component" => "component",
            "has_component_template" => "hasComponentTemplate",
            "get_component_template" => "getComponentTemplate"
        ];
    }

    /**
     * Push the component to the component stack.
     *
     * @param string $path Unique path to put the component in.
     */
    public function component(string $path)
    {
        if (!array_key_exists($path, $this->components)) {
            /** @var ComponentContainer $componentContainer */
            $componentContainer = pluginApp(ComponentContainer::class, ['originComponentTemplate' => $path]);

            EventDispatcher::fire(
                'Component.Import',
                [
                    $componentContainer
                ]
            );

            $this->components[$path] = empty(
            $componentContainer->getNewComponentTemplate()
            ) ? $componentContainer->getOriginComponentTemplate() : $componentContainer->getNewComponentTemplate();
        }
    }

    /**
     * Check whether a component template exists.
     *
     * @return bool
     */
    public function hasComponentTemplate(): bool
    {
        return !empty($this->components);
    }

    /**
     * Get the component from the component stack.
     *
     * @return string
     */
    public function getComponentTemplate(): string
    {
        return array_shift($this->components);
    }

}
