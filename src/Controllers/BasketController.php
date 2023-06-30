<?php //strict

namespace IO\Controllers;

use IO\Helper\RouteConfig;
use IO\Services\BasketService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;

/**
 * Class BasketController
 * @package IO\Controllers
 */
class BasketController extends LayoutController
{
    /**
     * Prepare and render the data for the basket
     * @param BasketService $basketService
     * @return string
     */
	public function showBasket(BasketService $basketService):string
	{
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        $shopBuilderRequest->setMainContentType('checkout');

		return $this->renderTemplate(
		    'tpl.basket',
            [],
            false
		);
	}

	public function redirect()
    {
        if(!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::BASKET);
    }
}
