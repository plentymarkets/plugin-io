<?php //strict

namespace LayoutCore\Helper;

use Plenty\Plugin\Events\Dispatcher;

/**
 * Class CategoryMap
 * @package LayoutCore\Helper
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
     * set category map
     * @param array $categoryMap
     */
	public function setCategoryMap(array $categoryMap)
	{
		$this->categoryMap = $categoryMap;
	}
    
    /**
     * get category id by key
     * @param string $key
     * @return int
     */
	public function getID(string $key):int
	{
		return (INT)$this->categoryMap[$key];
	}
}
