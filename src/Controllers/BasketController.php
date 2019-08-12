<?php //strict

namespace IO\Controllers;

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
		$basket = $basketService->getBasketForTemplate();

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        $shopBuilderRequest->setMainContentType('checkout');

		return $this->renderTemplate(
		    'tpl.basket',
			[
			    'basket' => $basket
            ],
            false
		);
	}
}
