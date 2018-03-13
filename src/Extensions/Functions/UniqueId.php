<?php //strict

namespace IO\Extensions\Functions;

use Plenty\Plugin\Events\Dispatcher;
use IO\Extensions\AbstractFunction;
use Plenty\Plugin\Http\Request;

class UniqueId extends AbstractFunction
{
    public function construct()
    {
    }

    /**
     * Return the available methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "uid" => "generateUniqueId"
        ];
    }

    public function generateUniqueId($prefix = "")
    {
        return uniqid($prefix);
    }
}