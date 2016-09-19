<?php //strict

namespace LayoutCore\Helper;

use Plenty\Plugin\Events\Dispatcher;

class CategoryMap
{
	/**
	 * @var array
	 */
	private $categoryMap = [];

	public function __construct(Dispatcher $event)
	{
		$event->fire(
			"init.categories",
			[$this]
		);
	}

	public function setCategoryMap(array $categoryMap)
	{
		$this->categoryMap = $categoryMap;
	}

	public function getID(string $key):int
	{
		return (INT)$this->categoryMap[$key];
	}
}
