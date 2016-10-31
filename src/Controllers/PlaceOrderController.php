<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Guards\AbstractGuard;
use LayoutCore\Services\NotificationService;
use LayoutCore\Services\OrderService;

/**
 * Class PlaceOrderController
 * @package LayoutCore\Controllers
 */
class PlaceOrderController extends LayoutController
{
    /**
     * @param OrderService $orderService
     * @param NotificationService $notificationService
     */
    public function placeOrder(OrderService $orderService, NotificationService $notificationService)
    {
        try {
            $orderService->placeOrder();
        }
        catch (\Exception $exception)
        {
            $notificationService->error($exception->getMessage());
            AbstractGuard::redirect("/checkout");
        }
        AbstractGuard::redirect("/confirmation");
    }
}
