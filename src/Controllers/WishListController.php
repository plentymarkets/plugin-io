<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

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
    public function showWishList():string
    {
        return $this->renderTemplate(
			"tpl.wish-list",
			[
				"wishs" => ""
			]
		);
    }
}
