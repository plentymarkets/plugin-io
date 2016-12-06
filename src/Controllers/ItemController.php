<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Helper\CategoryKey;
use IO\Services\ItemService;
use Plenty\Modules\Category\Models\Category;

/**
 * Class ItemController
 * @package IO\Controllers
 */
class ItemController extends LayoutController
{

	/**
	 * Prepare and render the item data.
	 * @param ItemService $itemService
	 * @param string $itemName The leading part of current request url before a-{itemId}.
	 * @param int $itemId The itemId read from current request url. Will be null if item url does not contain a slug.
	 * @return string
	 */
	public function showItem(
		ItemService $itemService,
		string $itemName = '',
		int $itemId = 0,
		int $variationId = 0
	):string
	{

		$currentItem = null;

		if((int)$variationId > 0)
		{
			$currentItem = $itemService->getVariation($variationId);
		}
		elseif($itemId > 0)
		{
			$currentItem = $itemService->getItem($itemId);
		}

		if($currentItem === null)
		{
			// If item not found, render the error category
			$itemNotFoundCategory = $this->categoryRepo->get(
				$this->categoryMap->getID(CategoryKey::ITEM_NOT_FOUND)
			);
			if($itemNotFoundCategory instanceof Category)
			{
				return $this->renderCategory($itemNotFoundCategory);
			}
			return '';
		}

		$this->categoryService->setCurrentCategoryID(
			$currentItem->variationStandardCategory->categoryId
		);

		return $this->renderTemplate(
			"tpl.item",
			[
				"item" => $currentItem
			]
		);
	}

	public function showItemWithoutName(ItemService $itemService, int $itemId, int $variationId):string
    {
        return $this->showItem( $itemService, "", $itemId, $variationId );
    }

	public function showItemFromAdmin(ItemService $itemService, int $itemId):string
	{
		return $this->showItem($itemService, "", $itemId, 0);
	}
}
