<?php //strict

namespace IO\Helper;

use Plenty\Plugin\Events\Dispatcher;

/**
 * Class CategoryMap
 * @package IO\Helper
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
			"init.categories",
			[$this]
		);
	}

    /**
     * Set the category map
     * @param array $categoryMap
     */
	public function setCategoryMap(array $categoryMap)
	{
		$this->categoryMap = $categoryMap;
	}

    /**
     * Get the category ID by key
     * @param string $key
     * @return int
     */
	public function getID(string $key):int
	{
		return (INT)$this->categoryMap[$key];
	}
}
