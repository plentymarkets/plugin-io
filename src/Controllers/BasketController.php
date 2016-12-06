<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\BasketService;

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
	public function showBasket(
		BasketService $basketService
	):string
	{
		$basket = $basketService->getBasket();

		return $this->renderTemplate(
			"tpl.basket",
			[
				"basket" => $basket
			]
		);
	}
}
