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
            $orderData = $orderService->placeOrder();
            AbstractGuard::redirect( "/execute-payment/" . $orderData->order->id );
        }
        catch (\Exception $exception)
        {
            $notificationService->error($exception->getMessage());
            AbstractGuard::redirect("/checkout");
        }
    }

    public function executePayment( OrderService $orderService, NotificationService $notificationService, int $orderId, int $paymentId = -1 )
    {
        // find order by id to check if order really exists
        $orderData = $orderService->findOrderById( $orderId );
        if( $orderData == null )
        {
            $notificationService->error("Order (". $orderId .") not found!");
            AbstractGuard::redirect("/checkout");
        }

        if( $paymentId < 0 )
        {
            // get payment id from order
            $paymentId = $orderData->order->methodOfPaymentId;
        }

        // execute payment
        try {
            $paymentResult = $orderService->executePayment($orderId, $paymentId);
            if ($paymentResult["type"] === "error")
            {
                // send errors
                $notificationService->error($paymentResult["value"]);
            }
        }
        catch(\Exception $exception)
        {
            $notificationService->error($exception->getMessage());
        }

        // show confirmation page, even if payment execution failed because order has already been replaced.
        // in case of failure, the order should have been marked as "not payed"
        AbstractGuard::redirect("/confirmation");
    }
}
