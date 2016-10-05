<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepository;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Repositories\Models\PaginatedResult;

use LayoutCore\Constants\CategoryType;
use LayoutCore\Services\ItemService;
use LayoutCore\Helper\CategoryMap;
use LayoutCore\Helper\CategoryKey;

/**
 * Class CategoryService
 * @package LayoutCore\Services
 */
class CategoryService
{
	/**
	 * @var CategoryRepository
	 */
	private $category;

	/**
	 * @var ItemService
	 */
	private $item;
	/**
	 * @var CategoryMap
	 */
	private $categoryMap;

	// is set from controllers
	/**
	 * @var Category
	 */
	private $currentCategory = null;
	/**
	 * @var array
	 */
	private $currentCategoryTree = [];

    /**
     * CategoryService constructor.
     * @param CategoryRepository $category
     * @param \LayoutCore\Services\ItemService $item
     * @param CategoryMap $categoryMap
     */
	public function __construct(CategoryRepository $category, ItemService $item, CategoryMap $categoryMap)
	{
		$this->category    = $category;
		$this->item        = $item;
		$this->categoryMap = $categoryMap;
	}

	/**
	 * Set the current category by ID.
	 * @param int $catID The id of the current category
	 */
	public function setCurrentCategoryID(int $catID = 0)
	{
		$this->setCurrentCategory(
			$this->category->get($catID)
		);
	}

	/**
	 * Set the current category by ID.
	 * @param Category $cat The current category
	 */
	public function setCurrentCategory($cat)
	{
		$this->currentCategory     = null;
		$this->currentCategoryTree = [];

		if($cat === null)
		{
			return;
		}

		// List parent/open categories
		$this->currentCategory = $cat;
		while($cat !== null)
		{
			$this->currentCategoryTree[$cat->level] = $cat;
			$cat                                    = $this->category->get($cat->parentCategoryId);
		}
	}

	/**
	 * Get a category by ID
	 * @param int $catID The category ID
	 * @param string $lang The language to get the category
	 * @return Category
	 */
	public function get($catID = 0, string $lang = "de")
	{
		return $this->category->get($catID, $lang);
	}

	/**
	 * Return the URL for a given category ID.
	 * @param Category $category the category to get the URL for
	 * @param string $lang the language to get the URL for
	 * @return string
	 */
	public function getURL($category, string $lang = "de"):string
	{
		if(!$category instanceof Category || $category->details[0] === null)
		{
			return "ERR";
		}
		return "/" . $this->category->getUrl($category->id, $lang);
	}

	/**
	 * Check whether a category is referenced by the current route
	 * @param int $catID The ID for the category to check
	 * @return bool
	 */
	public function isCurrent(Category $category):bool
	{
		if($this->currentCategory === null)
		{
			return false;
		}
		return $this->currentCategory->id === $category->id;
	}

	/**
	 * Check whether any child of a category is referenced by the current route
	 * @param int $catID The ID for the category to check
	 * @return bool
	 */
	public function isOpen(Category $category):bool
	{
		if($this->currentCategory === null)
		{
			return false;
		}

		foreach($this->currentCategoryTree as $lvl => $categoryBranch)
		{
			if($categoryBranch->id === $category->id)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Check whether a category or any of its children is referenced by the current route
	 * @param int $catID The ID for the category to check
	 * @return bool
	 */
	public function isActive(Category $category = null):bool
	{
        if($category instanceof Category)
        {
            return ($this->isCurrent($category) || $this->isOpen($category));
        }
        else
        {
            return false;
        }

	}

    /**
     * Check which category is the home category
     * @return bool
     */
	public function isHome():bool
	{
		return $this->currentCategory !== null && $this->currentCategory->id == $this->categoryMap->getID(CategoryKey::HOME);
	}

    /**
     * List the items of the specified category
     * @param null $category
     * @param int $defaultItemPerPage
     * @param int $variationShowType
     * @return null|PaginatedResult
     */
	public function getItems($category = null, int $defaultItemPerPage = 0, int $variationShowType = 1)
	{
		if(!$category instanceof Category)
		{
			return null;
		}
		return $this->item->getItemForCategory($category->id, $variationShowType);
	}

    /**
     * Get the category tree as a list
     * @param int $catID
     * @return array
     */
	public function getCategoryTreeAsList(int $catID = 0): array
	{
		$categoryTree = [];

		if($catID !== null)
		{
			$this->setCurrentCategoryID($catID);
		}

		for($i = 0; $i <= count($this->currentCategoryTree); $i++)
		{
			if($this->currentCategoryTree[$i] !== null)
			{
				$details                      = $this->currentCategoryTree[$i]->details[0];
				$categoryTree[$details->name] = $details->nameUrl;
			}
		}

		return $categoryTree;
	}
}
