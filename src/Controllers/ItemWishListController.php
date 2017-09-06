<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\ItemWishListService;
use Plenty\Plugin\ConfigRepository;

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
        $itemWishList = [];
    
        /**
         * @var ConfigRepository $configRepo
         */
        $configRepo = pluginApp(ConfigRepository::class);
        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );
        if(in_array('wish-list', $enabledRoutes))
        {
            $itemWishList = $itemWishListService->getItemWishList();
        }
        
        return $this->renderTemplate(
			"tpl.wish-list",
			[
                "wishList" => ( is_array($itemWishList) ? $itemWishList : [] )
			]
		);
    }
}
