<?php //strict

namespace LayoutCore\Extensions;

abstract class AbstractFilter
{
	/**
	 * @var array
	 */
	public static $filters = [];

	public function __construct()
	{
		array_push(self::$filters, $this);
	}

	public abstract function getFilters():array;
}
