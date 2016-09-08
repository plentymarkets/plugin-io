<?hh //strict

namespace LayoutCore\Extensions;

abstract class AbstractFunction
{
    public static array<AbstractFunction> $functions = array();

    public function __construct()
    {
        array_push( self::$functions, $this );
    }

    public abstract function getFunctions():array<string, string>;
}
