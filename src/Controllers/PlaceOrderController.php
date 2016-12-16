<?php //strict
namespace IO\Controllers;

use IO\Services\NotificationService;
use IO\Services\OrderService;
use Plenty\Plugin\Http\Response;

/**
 * Class PlaceOrderController
 * @package IO\Controllers
 */
class PlaceOrderController extends LayoutController
{
    /**
     * @param OrderService $orderService
     * @param NotificationService $notificationService
     * @param Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function placeOrder(
        OrderService $orderService,
        NotificationService $notificationService,
        Response $response)
    {
        try
        {
            $orderData = $orderService->placeOrder();
            return $response->redirectTo( "execute-payment/" . $orderData->order->id );
        }
        catch (\Exception $exception)
        {
            // TODO get better error text
            $notificationService->error($exception->getMessage());
            return $response->redirectTo("checkout");
        }
    }

    public function executePayment( OrderService $orderService, NotificationService $notificationService, Response $response, int $orderId, int $paymentId = -1 )
    {
        // find order by id to check if order really exists
        $orderData = $orderService->findOrderById( $orderId );
        if( $orderData == null )
        {
            $notificationService->error("Order (". $orderId .") not found!");
            return $response->redirectTo("checkout");
        }

        if( $paymentId < 0 )
        {
            // get payment id from order
            $paymentId = $orderData->order->methodOfPaymentId;
        }

        // execute payment
        try
        {
            $paymentResult = $orderService->executePayment($orderId, $paymentId);
            if ($paymentResult["type"] === "redirectUrl")
            {
                return $response->redirectTo($paymentResult["value"]);
            }
            elseif ($paymentResult["type"] === "error")
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
        return $response->redirectTo("confirmation");
    }
}
