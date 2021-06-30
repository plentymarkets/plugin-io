<?php //strict

namespace IO\Extensions\Functions;

use IO\Helper\EventDispatcher;
use IO\Extensions\AbstractFunction;

/**
 * Class Partial
 *
 * Contains twig functions that help with twig partial templates.
 *
 * @package IO\Extensions\Functions
 */
class Partial extends AbstractFunction
{
    private $partial = null;
    private $map = [];

    public function construct()
    {
    }

    /**
     * Sets partial twig template based on a key and value pair.
     *
     * @param string $name Name to set the value to.
     * @param string $value Value to set to the name.
     */
    public function set($name, $value)
    {
        $this->map[$name] = $value;
    }

    /**
     * Gets a set partial template based on the given key.
     *
     * @param string $key Key based on which the template is retrieved.
     * @return mixed
     */
    public function getTemplate($key)
    {
        return $this->map[$key];
    }

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "getPartial" => "getPartial"
        ];
    }

    /**
     * Gets partial twig template based on the passed key.
     *
     * @param string $key Key to get the partial twig template with.
     * @return mixed
     */
    public function getPartial($key)
    {
        if (!$this->partial instanceof Partial) {
            /** @var Partial $partial */
            $this->partial = pluginApp(Partial::class);
            EventDispatcher::fire('init.templates', [$this->partial]);
        }

        return $this->partial->getTemplate($key);
    }
}