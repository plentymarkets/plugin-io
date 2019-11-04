<?php //strict

namespace IO\Extensions\Functions;

use IO\Helper\EventDispatcher;
use IO\Extensions\AbstractFunction;

/**
 * Class Partial
 * @package IO\Extensions\Functions
 * @property string $footer
 * @property string $header
 * @property string $head
 * @property string $pageDesign
 */
class Partial extends AbstractFunction
{
    private $partial = null;
    private $map = [];

    public function construct()
    {
    }

    public function set($name, $value)
    {
       $this->map[$name] = $value;
    }

    public function getTemplate($key)
    {
        return $this->map[$key];
    }

    /**
     * Return the available methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "getPartial" => "getPartial"
        ];
    }

    public function getPartial($key)
    {
        if(!$this->partial instanceof Partial)
        {
            /** @var Partial $partial */
            $this->partial = pluginApp(Partial::class);
            EventDispatcher::fire('init.templates', [$this->partial]);
        }

        return $this->partial->getTemplate($key);
    }
}