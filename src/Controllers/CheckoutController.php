<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Services\BasketService;

/**
 * Class CheckoutController
 * @package LayoutCore\Controllers
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
