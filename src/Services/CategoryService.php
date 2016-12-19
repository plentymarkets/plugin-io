<?php //strict

namespace IO\Services;

use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;

use IO\Services\WebstoreConfigurationService;
use IO\Services\ItemService;
use IO\Helper\CategoryMap;
use IO\Helper\CategoryKey;
use IO\Builder\Category\CategoryParamsBuilder;

/**
 * Class CategoryService
 * @package IO\Services
 */
class CategoryService
{
	/**
	 * @var CategoryRepositoryContract
	 */
	private $categoryRepository;

    /**
     * @var WebstoreConfigurationService
     */
    private $webstoreConfig;

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
     * @param CategoryRepositoryContract $category
     */
	public function __construct(CategoryRepositoryContract $categoryRepository, WebstoreConfigurationService $webstoreConfig)
	{
		$this->categoryRepository    = $categoryRepository;
		$this->webstoreConfig 		 = $webstoreConfig;
	}

	/**
	 * Set the current category by ID.
	 * @param int $catID The id of the current category
	 */
	public function setCurrentCategoryID(int $catID = 0)
	{
		$this->setCurrentCategory(
			$this->categoryRepository->get($catID)
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
			$cat                                    = $this->categoryRepository->get($cat->parentCategoryId);
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
		return $this->categoryRepository->get($catID, $lang);
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
		return "/" . $this->categoryRepository->getUrl($category->id, $lang);
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
	 * @param Category $category The category to check
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
	 * @param Category $category The category to check
	 * @return bool
	 */
	public function isActive(Category $category = null):bool
	{
        return $category !== null && ($this->isCurrent($category) || $this->isOpen($category));
	}

    /**
     * Check which category is the home category
     * @return bool
     */
	public function isHome():bool
	{
		return $this->currentCategory !== null && $this->currentCategory->id == pluginApp(CategoryMap::class)->getID(CategoryKey::HOME);
	}

    /**
     * @param Category $category
     * @param array $params
     * @param int $page
     * @return null|PaginatedResult
     */
    public function getItems( $category = null, array $params = [], int $page = 1 )
    {
        if( $category == null )
        {
            $category = $this->currentCategory;
        }

        if( $category == null || $params == null )
        {
            return null;
        }

        return pluginApp(ItemService::class)->getItemForCategory( $category->id, pluginApp(CategoryParamsBuilder::class)->fromArray($params), $page );
    }

    /**
     * Return the sitemap tree as an array
     * @param string $type Only return categories of given type
     * @param string $lang The language to get sitemap tree for
     * @return array
     */
    public function getNavigationTree(string $type = "all", string $lang = "de"):array
    {
        return $this->categoryRepository->getLinklistTree($type, $lang, $this->webstoreConfig->getWebstoreConfig()->webstoreId);
    }
    /**
     * Return the sitemap list as an array
     * @param string $type Only return categories of given type
     * @param string $lang The language to get sitemap list for
     * @return array
     */
    public function getNavigationList(string $type = "all", string $lang = "de"):array
    {
        return $this->categoryRepository->getLinklistList($type, $lang, $this->webstoreConfig->getWebstoreConfig()->webstoreId);
    }

    /**
     * Returns a list of all parent categories including given category
     * @param int   $catID      The category Id to get the parents for or 0 to use current category
     * @param bool  $bottomUp   Set true to order result from bottom (deepest category) to top (= level 1)
     * @return array            The parents of the category
     */
	public function getHierarchy( int $catID = 0, bool $bottomUp = false ):array
    {
        if( $catID > 0 )
        {
            $this->setCurrentCategoryID( $catID );
        }

        $hierarchy = [];

        /**
         * @var Category $category
         */
        foreach ( $this->currentCategoryTree as $lvl => $category )
        {
            if( $category->linklist === 'Y' )
            {
                array_push( $hierarchy, $category );
            }
        }

        if( $bottomUp === false )
        {
            $hierarchy = array_reverse( $hierarchy );
        }

        return $hierarchy;
    }
}
