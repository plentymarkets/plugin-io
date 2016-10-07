<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;

use LayoutCore\Constants\CategoryType;
use LayoutCore\Constants\Language;

/**
 * Class NavigationService
 * @package LayoutCore\Services
 */
class NavigationService
{

	/**
	 * @var CategoryRepositoryContract
	 */
	private $categoryRepository;

    /**
     * NavigationService constructor.
     * @param CategoryRepositoryContract $categoryRepository
     */
	public function __construct(CategoryRepositoryContract $categoryRepository)
	{
		$this->categoryRepository = $categoryRepository;
	}

	/**
	 * Return the sitemap tree as an array
	 * @param string $type Only return categories of given type
	 * @param string $lang The language to get sitemap tree for
	 * @return array
	 */
	public function getNavigationTree(string $type = "all", string $lang = "de"):array
	{
		return $this->categoryRepository->getSitemapTree($type, $lang);
	}

	/**
	 * Return the sitemap list as an array
	 * @param string $type Only return categories of given type
	 * @param string $lang The language to get sitemap list for
	 * @return array
	 */
	public function getNavigationList(string $type = "all", string $lang = "de"):array
	{
		return $this->toArray($this->categoryRepository->getSitemapList($type, $lang));
	}

	// FIXME arrays of objects are not transformed to arrays of native types before passing to twig templates.
    /**
     * @param array $categories
     * @return array
     */
	private function toArray(array $categories):array
	{
		$result = [];
		foreach($categories as $category)
		{
			array_push($result, $category->toArray());
		}

		return $result;
	}

}
