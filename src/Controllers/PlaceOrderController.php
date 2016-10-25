<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Services\OrderService;

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
    public function showPlaceOrder(OrderService $orderService): string
    {
        $order = $orderService->placeOrder();



        return $this->renderTemplate(
            "tpl.place_order",
            [
                "place_order" => $order
            ]
        );
    }
}
