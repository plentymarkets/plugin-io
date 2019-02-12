<?php //strict
namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
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
            $url = "execute-payment/" . $orderData->order->id;
            $url .= UrlQuery::shouldAppendTrailingSlash() ? '/' : '';
            $url .= strlen($redirectParam) ? "?redirectParam=" . $redirectParam : '';

            return $this->urlService->redirectTo($url);
        }
        catch(BasketItemCheckException $exception)
        {
            if ($exception->getCode() == BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_ITEM)
            {
                $notificationService->warn('not enough stock for item', 9);
            }
            
            return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->checkout);
        }
        catch (\Exception $exception)
        {
            if($exception->getCode() === 15)
            {
                return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->confirmation);
            }

            // TODO get better error text
            $notificationService->error($exception->getMessage());
            return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->checkout);
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
            return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->checkout);
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

        return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->confirmation);
    }
}
