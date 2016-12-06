<?php //strict
namespace IO\Controllers;

use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Category\Models\Category;

use IO\Helper\CategoryKey;

/**
 * Class CategoryController
 * @package IO\Controllers
 */
class CategoryController extends LayoutController
{

	/**
	 * Prepare and render the data for categories
	 * @param string $lvl1 Level 1 of category url. Will be null at root page
	 * @param string $lvl2 Level 2 of category url.
	 * @param string $lvl3 Level 3 of category url.
	 * @param string $lvl4 Level 4 of category url.
	 * @param string $lvl5 Level 5 of category url.
	 * @param string $lvl6 Level 6 of category url.
	 * @return string
	 */
	public function showCategory(
		$lvl1 = null,
		$lvl2 = null,
		$lvl3 = null,
		$lvl4 = null,
		$lvl5 = null,
		$lvl6 = null):string
	{
		// Get the current category
		if($lvl1 === null)
		{
			// Get the start page ID from the layout plugin config
			$currentCategory = $this->categoryRepo->get(
				$this->categoryMap->getID(CategoryKey::HOME)
			);
		}
		else
		{
			$currentCategory = $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6);
		}
        
        return $this->renderCategory($currentCategory);
	}

}
