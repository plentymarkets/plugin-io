<?hh //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Helper\CategoryKey;
use LayoutCore\Services\ItemService;

class ItemController extends LayoutController {

    /**
     * Prepare and render item data.
     * @param ItemService $itemService
     * @param string $itemName The leading part of current request url before a-{itemId}.
     * @param ?int $itemId The itemId read from current request url. Will be null if item url does not contain a slug.
     */
    public function showItem(
        ItemService $itemService,
        string $itemName='',
        int $itemId=0,
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

        if( $currentItem === null ) {
            // item not found --> render error category
            $itemNotFoundCategory = $this->categoryRepo->get(
                $this->categoryMap->getID( CategoryKey::ITEM_NOT_FOUND )
            );
            return $this->renderCategory( $itemNotFoundCategory );
        }

        $this->categoryService->setCurrentCategoryID(
            $currentItem->variationStandardCategory->categoryId
        );

        return $this->renderTemplate(
            "tpl.item",
            array(
                "item" => $currentItem
            )
        );
    }

    public function showItemFromAdmin( ItemService $itemService, int $itemId ):string
    {
        return $this->showItem($itemService, "", $itemId, 0);
    }
}
