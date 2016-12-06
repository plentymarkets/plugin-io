<?php //strict

namespace IO\Extensions;

/**
 * Class AbstractFunction
 * @package IO\Extensions
 */
abstract class AbstractFunction
{
	/**
	 * @var array
	 */
	public static $functions = [];

    /**
     * AbstractFunction constructor.
     */
	public function __construct()
	{
		array_push(self::$functions, $this);
	}

    /**
     * Return the available functions
     * @return array
     */
	public abstract function getFunctions():array;
}
