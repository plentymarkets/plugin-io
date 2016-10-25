<?php //strict
namespace LayoutCore\Controllers;

/**
 * Class PlaceOrderController
 * @package LayoutCore\Controllers
 */
class PlaceOrderController extends LayoutController
{
    /**
     * Prepare and render the data for the my account page
     * @return string
     */
    public function showPlaceOrder(): string
    {
        return $this->renderTemplate(
            "tpl.place_order",
            [
                "place_order" => ""
            ]
        );
    }
}
