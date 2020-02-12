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
        if(!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::WISH_LIST);
    }
}
