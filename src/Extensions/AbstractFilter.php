<?php

namespace IO\Extensions;

/**
 * Class AbstractFilter
 *
 * @package IO\Extensions
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\Filters\AbstractFilter
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
    public abstract function getFilters(): array;
}
