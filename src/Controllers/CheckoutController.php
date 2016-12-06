<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\BasketService;

/**
 * Class CheckoutController
 * @package IO\Controllers
 */
class CheckoutController extends LayoutController
{
    /**
     * Prepare and render the data for the checkout
     * @param BasketService $basketService
     * @return string
     */
	public function showCheckout(BasketService $basketService): string
	{
		$basket = $basketService->getBasket();

		return $this->renderTemplate(
			"tpl.checkout",
			[
				"basket" => $basket
			]
		);
	}
}
