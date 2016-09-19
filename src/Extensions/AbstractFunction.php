<?php //strict

namespace LayoutCore\Extensions;

abstract class AbstractFunction
{
	/**
	 * @var array
	 */
	public static $functions = [];

	public function __construct()
	{
		array_push(self::$functions, $this);
	}

	public abstract function getFunctions():array;
}
