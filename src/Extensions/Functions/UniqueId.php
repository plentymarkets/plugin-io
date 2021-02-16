<?php //strict

namespace IO\Extensions\Functions;

use Plenty\Plugin\Events\Dispatcher;
use IO\Extensions\AbstractFunction;
use Plenty\Plugin\Http\Request;

/**
 * Class UniqueId
 *
 * Contains global helper function to get a unique ID from.
 *
 * @package IO\Extensions\Functions
 */
class UniqueId extends AbstractFunction
{
    public function construct()
    {
    }

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "uid" => "generateUniqueId"
        ];
    }

    /**
     * Gets an unique ID with an optional prefix.
     *
     * @param string $prefix Optional prefix that gets attached to the unique ID.
     * @return string
     */
    public function generateUniqueId($prefix = "")
    {
        return uniqid($prefix);
    }
}
