<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;
use IO\Services\ItemWishListService;

/**
 * Class WishListController
 * @package IO\Controllers
 */
class WishListController extends LayoutController
{
    /**
     * Render the wish list
     * @return string
     */
    public function showWishList(ItemWishListService $itemWishListService, CustomerService $customerService):string
    {
        $wishList = [];

        if($customerService->getContactId() > 0)
        {
            $wishList = $itemWishListService->getItemWishListForContact();
        }

        return $this->renderTemplate(
			"tpl.wish-list",
			[
				"wishList" => $wishList
			]
		);
    }
}
