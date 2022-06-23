<?php //strict

namespace IO\Controllers;

use IO\Builder\Order\AddressType;
use IO\Constants\LogLevel;
use IO\Extensions\Constants\ShopUrls;
use IO\Extensions\Filters\ItemNameFilter;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Services\NotificationService;
use IO\Services\OrderService;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\Events\AfterBasketItemToOrderItem;
use Plenty\Modules\Webshop\Events\ValidateVatNumber;
use Plenty\Modules\Webshop\Helpers\UrlQuery;
use Plenty\Plugin\Events\Dispatcher;
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
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     * @param ShopUrls $shopUrls
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function placeOrder(
        OrderService $orderService,
        NotificationService $notificationService,
        SessionStorageRepositoryContract $sessionStorageRepository,
        ShopUrls $shopUrls
    ) {
        try {
            /** @var Dispatcher $eventDispatcher */
            $eventDispatcher = pluginApp(Dispatcher::class);
            /** @var ValidateVatNumber $val */

            $eventDispatcher->listen(
                AfterBasketItemToOrderItem::class,
                function ($event) {
                    /** @var ItemNameFilter $itemNameFilter */
                    $itemNameFilter = pluginApp(ItemNameFilter::class);
                    $basketItem = $event->getBasketItem();
                    $orderItem = $event->getOrderItem();
                    $orderItem['orderItemName'] = $itemNameFilter->itemName($basketItem['variation']['data']);
                    return $orderItem;
                }
            );

            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);

            /** @var BasketService $basketService */
            $basketService = pluginApp(BasketService::class);

            $billingAddressId = $basketService->getBillingAddressId();
            if (!is_null($billingAddressId)) {
                $billingAddressData = $customerService->getAddress($billingAddressId, AddressType::BILLING);
                $vatOption = $billingAddressData->options->where('typeId', AddressOption::TYPE_VAT_NUMBER)->first();
                if (!is_null($vatOption) && strlen(trim($vatOption->value)) > 0) {
                    $val = pluginApp(ValidateVatNumber::class, [trim($vatOption->value), $billingAddressData->countryId]);
                    $eventDispatcher->fire($val);
                }
            }

            $deliveryAddressId = $basketService->getDeliveryAddressId();
            if (!is_null($deliveryAddressId) && $deliveryAddressId > 0) {
                $deliveryAddressData = $customerService->getAddress($deliveryAddressId, AddressType::DELIVERY);
                $vatOption = $deliveryAddressData->options->where('typeId', AddressOption::TYPE_VAT_NUMBER)->first();
                if (!is_null($vatOption) && strlen(trim($vatOption->value)) > 0) {
                    $val = pluginApp(ValidateVatNumber::class, [trim($vatOption->value), $deliveryAddressData->countryId]);
                    $eventDispatcher->fire($val);
                }
            }
        } catch (\Exception $exception) {
            return $this->handlePlaceOrderException($exception);
        }

        //check if an order has already been placed in the last 30 seconds
        $lastPlaceOrderTry = $sessionStorageRepository->getSessionValue(
            SessionStorageRepositoryContract::LAST_PLACE_ORDER_TRY
        );

        if (!is_null($lastPlaceOrderTry) && time() < (int)$lastPlaceOrderTry + self::ORDER_RETRY_INTERVAL) {
            //place order has been called a second time in a time frame of 30 seconds
            $notificationService->addNotificationCode(LogLevel::ERROR, 115);
            return $this->urlService->redirectTo($shopUrls->checkout);
        }
        $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::LAST_PLACE_ORDER_TRY, time());

        try {
            $orderData = $orderService->placeOrder();
        } catch (\Exception $exception) {
            return $this->handlePlaceOrderException($exception);
        }

        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.PlaceOrderController_orderCreated",
            [
                "order" => $orderData->order,
            ]
        );

        if(!is_null(ValidateVatNumber::getFallbackStatusServiceUnavailable())) {
            /**
             * @var NotificationService $notificationService
             */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode('warn', 212);
        }
        try {
            $orderService->complete($orderData->order);
        } catch (\Exception $exception) {
            // This should never happen since every exception should be caught inside this function!
            $this->getLogger(__CLASS__)->error(
                "IO::Debug.PlaceOrderController_cannotCompleteOrder",
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage()
                ]
            );
        }

        $request = pluginApp(Request::class);
        $redirectParam = $request->get('redirectParam', '');
        $urlParams = [];
        $url = "execute-payment/" . $orderData->order->id;
        $url .= UrlQuery::shouldAppendTrailingSlash() ? '/' : '';

        if (strlen($redirectParam)) {
            $urlParams['redirectParam'] = $redirectParam;
        }

        if ($sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::READONLY_CHECKOUT) === true) {
            $urlParams['readonlyCheckout'] = true;
        }

        if (count($urlParams)) {
            $paramString = http_build_query($urlParams);
            if (strlen($paramString)) {
                $url .= '?' . $paramString;
            }
        }

        return $this->urlService->redirectTo($url);
    }

    public function executePayment(
        OrderService $orderService,
        NotificationService $notificationService,
        ShopUrls $shopUrls,
        int $orderId,
        int $paymentId = -1
    ) {
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
        $orderData = $orderService->findOrderById($orderId);
        if ($orderData == null) {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.PlaceOrderController_orderNotDefined",
                [
                    "orderId" => $orderId,
                    "paymentId" => $paymentId
                ]
            );
            $notificationService->error("Order (" . $orderId . ") not found!");
            return $this->urlService->redirectTo($shopUrls->checkout);
        }

        if ($paymentId < 0) {
            // get payment id from order
            $paymentId = $orderData->order->methodOfPaymentId;
        }

        // execute payment
        try {
            $paymentResult = $orderService->executePayment($orderId, $paymentId);
            if ($paymentResult["type"] === "redirectUrl") {
                $this->getLogger(__CLASS__)->info(
                    "IO::Debug.PlaceOrderController_redirectToPaymentResult",
                    [
                        "paymentResult" => $paymentResult
                    ]
                );
                return $this->urlService->redirectTo($paymentResult["value"]);
            } elseif ($paymentResult["type"] === "error") {
                $this->getLogger(__CLASS__)->warning(
                    "IO::Debug.PlaceOrderController_errorFromPaymentProvider",
                    [
                        "paymentResult" => $paymentResult
                    ]
                );
                // send errors
                $notificationService->error($paymentResult["value"]);
            }
        } catch (\Exception $exception) {
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
        if (strlen($redirectParam)) {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.PlaceOrderController_redirectToParam",
                [
                    "redirectParam" => $redirectParam
                ]
            );
            return $this->urlService->redirectTo($redirectParam);
        }
        return $this->urlService->redirectTo($shopUrls->confirmation);
    }


    private function handlePlaceOrderException(\Exception $exception)
    {
        /**
         * @var NotificationService $notificationService
         */
        $notificationService = pluginApp(NotificationService::class);
        /**
         * @var ShopUrls $shopUrls
         */
        $shopUrls = pluginApp(ShopUrls::class);

        if ($exception instanceof BasketItemCheckException) {
            if ($exception->getCode() == BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_ITEM) {
                $notificationService->warn('not enough stock for item', 9);
            } elseif ($exception->getCode() == BasketItemCheckException::COUPON_REQUIRED) {
                $notificationService->error('promotion coupon required', 501);
            } elseif ($exception->getCode() == BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_VARIATIONS) {
                $additionalData = $exception->getAdditionalData();
                $itemsWithoutStock = $additionalData['itemsWithoutStock'];
                $basketItems = $additionalData['basketItems'];

                /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);

                foreach ($itemsWithoutStock as $itemWithoutStock) {
                    $filteredWithoutStock = array_filter($basketItems, function ($filterItem) use ($itemWithoutStock) {
                        return $filterItem['id'] == $itemWithoutStock['item']['id'];
                    });
                    $updatedItem = array_shift($filteredWithoutStock);

                    $quantity = $itemWithoutStock['stockNet'];

                    if ($quantity <= 0 && (int)$updatedItem['id'] > 0) {
                        $basketService->deleteBasketItem($updatedItem['id']);
                    } elseif ((int)$updatedItem['id'] > 0) {
                        $updatedItem['quantity'] = $quantity;
                        $basketService->updateBasketItem($updatedItem['id'], $updatedItem);
                    }
                }

                $notificationService->warn('not enough stock for item', 9);
            }
        } elseif ($exception->getCode() === 15) {
            // No baskets items found because basket has already been cleared after previous try of placing an order.
            // Order should already been created => redirect to confirmation page
            return $this->urlService->redirectTo($shopUrls->confirmation);
        } elseif (in_array($exception->getCode(), [210, 211])) {
            $notificationService->error($exception->getMessage(), $exception->getCode());
        } else {
            // TODO get better error text
            $notificationService->error($exception->getMessage());
        }

        return $this->urlService->redirectTo($shopUrls->checkout);
    }
}
