<?php //strict

namespace IO\Helper;

use Plenty\Plugin\Events\Dispatcher;

/**
 * Class CategoryMap
 *
 * This class was previously used to build a map of categories.
 *
 * @package IO\Helper
 * @deprecated
 */
class CategoryMap
{
	/**
	 * @var array
	 */
	private $categoryMap = [];

    /**
     * CategoryMap constructor.
     * @param Dispatcher $event
     */
	public function __construct(Dispatcher $event)
	{
		$event->fire(
			"IO.init.categories",
			[$this]
		);
	}

    /**
     * Set the category map.
     * @param array $categoryMap
     * @deprecated
     */
	public function setCategoryMap(array $categoryMap)
	{
		$this->categoryMap = $categoryMap;
	}

    /**
     * Get the category ID by key.
     * @param string $key
     * @return int
     * @deprecated
     */
	public function getID(string $key):int
	{
		return (INT)$this->categoryMap[$key];
	}
}
