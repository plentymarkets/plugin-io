<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Guards\AbstractGuard;
use LayoutCore\Services\OrderService;

/**
 * Class PlaceOrderController
 * @package LayoutCore\Controllers
 */
class PlaceOrderController extends LayoutController
{
    /**
     * @param OrderService $orderService
     */
    public function placeOrder(OrderService $orderService)
    {
        try {
            $orderService->placeOrder();
        }
        catch (\Exception $exception)
        {
            AbstractGuard::redirect("/checkout");
            // TODO error message
        }

        AbstractGuard::redirect("/confirmation");
    }
}
