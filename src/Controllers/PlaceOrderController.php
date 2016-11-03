<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Services\NotificationService;
use LayoutCore\Services\OrderService;
use Plenty\Plugin\Http\Response;

/**
 * Class PlaceOrderController
 * @package LayoutCore\Controllers
 */
class PlaceOrderController extends LayoutController
{
    /**
     * @param OrderService $orderService
     * @param NotificationService $notificationService
     * @param Response $response
     * @return \Symfony\Component\HttpFoundation\Response|void
     * @internal param Response $response
     */
    public function placeOrder(
        OrderService $orderService,
        NotificationService $notificationService,
        Response $response
    )
    {
        try {
            $orderService->placeOrder();
        }
        catch (\Exception $exception)
        {
            // TODO get better error text
            $notificationService->error($exception->getMessage());

            return $response->redirectTo("checkout");
        }
        return $response->redirectTo("confirmation");
    }
}
