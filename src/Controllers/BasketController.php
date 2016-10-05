<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Services\BasketService;

/**
 * Class BasketController
 * @package LayoutCore\Controllers
 */
class BasketController extends LayoutController
{
    /**
     * prepare and render data for basket
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
