<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Services\BasketService;


class BasketController extends LayoutController
{
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
