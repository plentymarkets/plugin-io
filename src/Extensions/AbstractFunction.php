<?php //strict

namespace LayoutCore\Extensions;

/**
 * Class AbstractFunction
 * @package LayoutCore\Extensions
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
