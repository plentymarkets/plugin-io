<?php //strict
namespace IO\Controllers;

use IO\Services\NotificationService;
use IO\Services\OrderService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;

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

        $request = pluginApp(Request::class);
        $redirectParam = $request->get('redirectParam', '');

        try
        {
            $orderData = $orderService->placeOrder();
            return $this->urlService->redirectTo( "execute-payment/" . $orderData->order->id . (strlen($redirectParam) ? "/?redirectParam=" . $redirectParam : '') );
        }
        catch (\Exception $exception)
        {
            // TODO get better error text
            $notificationService->error($exception->getMessage());
            return $this->urlService->redirectTo("checkout");
        }
    }

    public function executePayment( OrderService $orderService, NotificationService $notificationService, Response $response, int $orderId, int $paymentId = -1 )
    {
        $request = pluginApp(Request::class);
        $redirectParam = $request->get('redirectParam', '');

        // find order by id to check if order really exists
        $orderData = $orderService->findOrderById( $orderId );
        if( $orderData == null )
        {
            $notificationService->error("Order (". $orderId .") not found!");
            return $this->urlService->redirectTo("checkout");
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
                return $this->urlService->redirectTo($paymentResult["value"]);
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
        if( strlen($redirectParam) )
        {
            return $this->urlService->redirectTo($redirectParam);
        }

        return $this->urlService->redirectTo("confirmation");
    }
}
