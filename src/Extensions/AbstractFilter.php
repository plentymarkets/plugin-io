<?php //strict

namespace IO\Extensions;

/**
 * Class AbstractFilter
 * @package IO\Extensions
 */
abstract class AbstractFilter
{
	/**
	 * @var array
	 */
	public static $filters = [];

    /**
     * AbstractFilter constructor.
     */
	public function __construct()
	{
		array_push(self::$filters, $this);
	}

    /**
     * Return the available filter methods
     * @return array
     */
	public abstract function getFilters():array;
}
