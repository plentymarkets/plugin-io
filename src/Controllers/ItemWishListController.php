<?php //strict
namespace IO\Controllers;

use IO\Helper\RouteConfig;

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
    public function showWishList():string
    {
        return $this->renderTemplate(
			"tpl.wish-list",
			[
                "object" => ""
            ],
            true
		);
    }

    public function redirect()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::WISH_LIST);
    }
}
