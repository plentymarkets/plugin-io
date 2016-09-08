<?hh //strict

namespace LayoutCore\Extensions;

abstract class AbstractFilter
{
    public static array<AbstractFilter> $filters = array();

    public function __construct()
    {
        array_push( self::$filters, $this );
    }

    public abstract function getFilters():array<string, string>;
}
