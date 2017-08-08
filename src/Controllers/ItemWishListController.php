<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\ItemWishListService;

/**
 * Class WishListController
 * @package IO\Controllers
 */
class ItemWishListController extends LayoutController
{
    /**
     * Render the wish list
     * @return string
     */
    public function showWishList(ItemWishListService $itemWishListService):string
    {
        $itemWishList = $itemWishListService->getItemWishList();

        return $this->renderTemplate(
			"tpl.wish-list",
			[
                "wishList" => ( is_array($itemWishList) ? $itemWishList : [] )
			]
		);
    }
}
