<?php //strict
namespace IO\Controllers;

use IO\Constants\LogLevel;
use IO\Constants\SessionStorageKeys;
use IO\Extensions\Constants\ShopUrls;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use IO\Services\SessionStorageService;
use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\Loggable;

/**
 * Class PlaceOrderController
 * @package IO\Controllers
 */
class PlaceOrderController extends LayoutController
{
    const ORDER_RETRY_INTERVAL = 30;
    
    use Loggable;

    /**
     * @param OrderService $orderService
     * @param NotificationService $notificationService
     * @param SessionStorageService $sessionStorageService
     * @param Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function placeOrder(
        OrderService $orderService,
        NotificationService $notificationService,
        SessionStorageService $sessionStorageService,
        Response $response)
    {
        $request = pluginApp(Request::class);
        $redirectParam = $request->get('redirectParam', '');
        
        try
        {
            //check if an order has already been placed in the last 30 seconds
            $lastPlaceOrderTry = $sessionStorageService->getSessionValue(SessionStorageKeys::LAST_PLACE_ORDER_TRY);
            if (is_null($lastPlaceOrderTry) ||
                ((int)$lastPlaceOrderTry > 0 && time() > (int)$lastPlaceOrderTry + self::ORDER_RETRY_INTERVAL))
            {
                $sessionStorageService->setSessionValue(SessionStorageKeys::LAST_PLACE_ORDER_TRY, time());
                $orderData = $orderService->placeOrder();
                $urlParams = [];
                $url = "execute-payment/" . $orderData->order->id;
                $url .= UrlQuery::shouldAppendTrailingSlash() ? '/' : '';
                
                if(strlen($redirectParam))
                {
                    $urlParams['redirectParam'] = $redirectParam;
                }
                
                if($sessionStorageService->getSessionValue(SessionStorageKeys::READONLY_CHECKOUT) === true)
                {
                    $urlParams['readonlyCheckout'] = true;
                }
                
                if(count($urlParams))
                {
                    $paramString = http_build_query($urlParams);
                    if(strlen($paramString))
                    {
                        $url .= '?'.$paramString;
                    }
                }
    
                return $this->urlService->redirectTo($url);
            }
            else
            {
                throw new \Exception('order retry time not reached', 115);
            }
            
        }
        catch(BasketItemCheckException $exception)
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.PlaceOrderController_cannotPlaceOrder",
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage()
                ]
            );
            if($exception->getCode() == BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_ITEM)
            {
                $notificationService->warn('not enough stock for item', 9);
            }
            
            return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->checkout);
        }
        catch (\Exception $exception)
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.PlaceOrderController_cannotPlaceOrder",
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage()
                ]
            );

            if($exception->getCode() === 15)
            {
                return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->confirmation);
            }
            elseif($exception->getCode() == 115)
            {
                //place order has been called a second time in a time frame of 30 seconds
                $notificationService->addNotificationCode(LogLevel::ERROR, $exception->getCode());
                return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->checkout);
            }

            // TODO get better error text
            $notificationService->error($exception->getMessage());
            return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->checkout);
        }
    }

    public function executePayment( OrderService $orderService, NotificationService $notificationService, Response $response, int $orderId, int $paymentId = -1 )
    {
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.PlaceOrderController_executePayment",
            [
                "orderId" => $orderId,
                "paymentId" => $paymentId
            ]
        );

        $request = pluginApp(Request::class);
        $redirectParam = $request->get('redirectParam', '');

        // find order by id to check if order really exists
        $orderData = $orderService->findOrderById( $orderId );
        if( $orderData == null )
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.PlaceOrderController_orderNotDefined",
                [
                    "orderId" => $orderId,
                    "paymentId" => $paymentId
                ]
            );
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
                $this->getLogger(__CLASS__)->info(
                    "IO::Debug.PlaceOrderController_redirectToPaymentResult",
                    [
                        "paymentResult" => $paymentResult
                    ]
                );
                return $this->urlService->redirectTo($paymentResult["value"]);
            }
            elseif ($paymentResult["type"] === "error")
            {
                $this->getLogger(__CLASS__)->warning(
                    "IO::Debug.PlaceOrderController_errorFromPaymentProvider",
                    [
                        "paymentResult" => $paymentResult
                    ]
                );
                // send errors
                $notificationService->error($paymentResult["value"]);
            }
        }
        catch(\Exception $exception)
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.PlaceOrderController_cannotExecutePayment",
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage()
                ]
            );
            $notificationService->error($exception->getMessage());
        }

        // show confirmation page, even if payment execution failed because order has already been replaced.
        // in case of failure, the order should have been marked as "not payed"
        if( strlen($redirectParam) )
        {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.PlaceOrderController_redirectToParam",
                [
                    "redirectParam" => $redirectParam
                ]
            );
            return $this->urlService->redirectTo($redirectParam);
        }
        return $this->urlService->redirectTo(pluginApp(ShopUrls::class)->confirmation);
    }
}
