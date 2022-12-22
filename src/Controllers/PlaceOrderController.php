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
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Item\VariationBundle\Contracts\VariationBundleRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

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
                $itemsWithoutStockUnordered = $additionalData['itemsWithoutStock'];
                $basketItems = $additionalData['basketItems'];

                //prepend the simple item variations and append the rest(sets and bundles)
                $orderedIndexArr = [];
                $simpleItemsExist = false;
                foreach ($itemsWithoutStockUnordered as $key => $itemWithoutStock) {
                    if ($itemWithoutStock['item']['itemType'] === 0) {
                        array_unshift($orderedIndexArr, $key);
                        $simpleItemsExist = true;
                    } elseif ($itemWithoutStock['item']['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET ||
                        $itemWithoutStock['item']['itemType'] === BasketItem::BASKET_ITEM_TYPE_BUNDLE
                    ) {
                        $orderedIndexArr[] = $key;
                    }
                }

                //if there are no simple items in the basket we should not even bother to order
                if ($simpleItemsExist) {
                    $itemsWithoutStock = array_replace(array_flip($orderedIndexArr), $itemsWithoutStockUnordered);
                } else {
                    $itemsWithoutStock = $additionalData['itemsWithoutStock'];
                }

                $totalVariationQuantities = $this->getTotalBasketItemQuantities($itemsWithoutStock, $basketItems);

                /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);

                $alreadyUpdatedVariationIds = [];
                foreach ($itemsWithoutStock as $itemWithoutStock) {
                    if ($itemWithoutStock['item']['itemType'] !== BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT &&
                        $itemWithoutStock['item']['itemType'] !== BasketItem::BASKET_ITEM_TYPE_BUNDLE_COMPONENT) {

                        $variationsToCompareWith = [];
                        foreach ($alreadyUpdatedVariationIds as $item) {
                            $variationsToCompareWith[$item['variationId']]['updatedQuantity'] += $item['updatedQuantity'];
                            $variationsToCompareWith[$item['variationId']]['stockLeft'] = $item['stockLeft'];
                        }

                        $updatedArray = array_filter(
                            $basketItems,
                            function ($filterItem) use ($itemWithoutStock) {
                                return $filterItem['id'] == $itemWithoutStock['item']['id'];
                            }
                        );

                        $updatedItem = array_shift($updatedArray);

                        $quantity = $itemWithoutStock['stockNet'];

                        $itemComponents = [];
                        if ($updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET) {
                            $itemComponents = $itemWithoutStock['item']['setComponents'];
                        } elseif ($updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_BUNDLE) {
                            $itemComponents = $this->getBundleComponents($updatedItem);
                        }

                        //if the variationId was already updated
                        if (array_key_exists($updatedItem['variationId'], $variationsToCompareWith)) {
                            continue;
                        } else {
                            //check if it's a set item
                            $variationAlreadyUpdated = false;
                            if ($updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET ||
                                $updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_BUNDLE
                            ) {
                                //get the set components and see if one of it is in the $alreadyUpdatedVariationIds
                                foreach ($itemComponents as $itemComponent) {
                                    if (array_key_exists($itemComponent['variationId'], $variationsToCompareWith) &&
                                        $variationsToCompareWith[$itemComponent['variationId']]['stockLeft'] >= $totalVariationQuantities[$itemComponent['variationId']] - $variationsToCompareWith[$itemComponent['variationId']]['updatedQuantity']
                                    ) {
                                        $variationAlreadyUpdated = true;
                                    }
                                }
                            }

                            if ($variationAlreadyUpdated) {
                                continue;
                            }
                        }

                        if ($quantity <= 0 && (int)$updatedItem['id'] > 0) {
                            //if the action of delete item is happening we need to add those variationIds of
                            // the set or bundle components to $alreadyUpdatedVariationIds
                            $alreadyUpdatedVariationIds = array_merge(
                                $alreadyUpdatedVariationIds,
                                $this->getVariationIds($updatedItem, $itemComponents)
                            );
                            $basketService->deleteBasketItem($updatedItem['id']);
                        } elseif ((int)$updatedItem['id'] > 0 && $quantity !== $itemWithoutStock['item']['quantity']) {
                            //if the update of item is happening we need to add those set components to $alreadyUpdatedVariationIds
                            $updatedItem['quantity'] = $quantity;
                            $subtractedQty = $itemWithoutStock['item']['quantity'] - $quantity;
                            $alreadyUpdatedVariationIds = array_merge(
                                $alreadyUpdatedVariationIds,
                                $this->getVariationIds($updatedItem, $itemComponents, $subtractedQty)
                            );
                            $basketService->updateBasketItem($updatedItem['id'], $updatedItem);
                        }
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


    /**
     * @param array $updatedItem
     * @param array $itemComponents
     * @return array
     */
    private function getVariationIds(array $updatedItem, array $itemComponents,  $subtractedQty = 0)
    {
        $result = [];
        if ($updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET ||
            $updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_BUNDLE
        ) {
            foreach ($itemComponents as $itemComponent) {
                $result[] = [
                    'variationId' => $itemComponent['variationId'],
                    'updatedQuantity' => ($subtractedQty == 0) ? $updatedItem['quantity'] : $subtractedQty,
                    'stockLeft' => $this->getStockLeft($itemComponent['variationId'])->getVariationStock()->stockNet
                ];
            }
        } else {
            $result[] = [
                'variationId' => $updatedItem['variationId'],
                'updatedQuantity' => ($subtractedQty == 0) ? $updatedItem['quantity'] : $subtractedQty,
                'stockLeft' => $this->getStockLeft($updatedItem['variationId'])->getVariationStock()->stockNet
            ];
        }

        return $result;
    }


    /**
     * @param int $variationId
     * @return mixed
     */
    private function getStockLeft(int $variationId)
    {
        $columns = [
            'variationStock' => [
                'fields' => [
                    'stockNet',
                ],
                'params' => [
                    'type' => 'virtual',
                ],
            ],
            'variationBase' => [
                'id',
                'limitOrderByStockSelect',
            ],
        ];

        $filter = [
            'variationBase.hasId' => ['id' => $variationId],
        ];

        /** @var ItemDataLayerRepositoryContract $itemDataLayer */
        $itemDataLayer = pluginApp(ItemDataLayerRepositoryContract::class);

        return $itemDataLayer->search($columns, $filter)->current();
    }

    /**
     * @param array $itemsWithoutStockUnordered
     * @param array $basketItems
     * @return array
     * @throws \Throwable
     */
    private function getTotalBasketItemQuantities(array $itemsWithoutStockUnordered, array $basketItems)
    {
        $result = [];
        foreach ($itemsWithoutStockUnordered as $key => $itemWithoutStock) {
            if ($itemWithoutStock['item']['itemType'] !== BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT &&
                $itemWithoutStock['item']['itemType'] !== BasketItem::BASKET_ITEM_TYPE_BUNDLE_COMPONENT) {

                $updatedArray = array_filter(
                    $basketItems,
                    function ($filterItem) use ($itemWithoutStock) {
                        return $filterItem['id'] == $itemWithoutStock['item']['id'];
                    }
                );

                $updatedItem = array_shift($updatedArray);

                if ($updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET) {
                    $itemComponents = $itemWithoutStock['item']['setComponents'];
                    foreach ($itemComponents as $item) {
                        $result[$item['variationId']] += $itemWithoutStock['item']['quantity'];
                    }
                } elseif ($updatedItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_BUNDLE) {
                    $itemComponents = $this->getBundleComponents($updatedItem);
                    foreach ($itemComponents as $item) {
                        $result[$item['variationId']] += $itemWithoutStock['item']['quantity'];
                    }
                } else {
                    $result[$itemWithoutStock['item']['variationId']] += $itemWithoutStock['item']['quantity'];
                }
            }
        }

        return $result;
    }

    /**
     * Get all components of the bundle
     *
     * @param $basketItem
     * @return array
     * @throws \Throwable
     */
    private function getBundleComponents($basketItem)
    {
        $variationIds = [];

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        /** @var VariationBundleRepositoryContract $bundleRepository */
        $variationBundleRepository = pluginApp( VariationBundleRepositoryContract::class );

        $bundleItems = $authHelper->processUnguarded(function() use ($variationBundleRepository, $basketItem) {
            return $variationBundleRepository->findByVariationId($basketItem['variationId']);
        });

        if(count($bundleItems)) {
            foreach ($bundleItems->toArray() as $bundleItem) {
                if($bundleItem['componentVariationId'] !== $basketItem['variationId']) {
                    $variationIds[] = [
                        'variationId' => $bundleItem['componentVariationId']
                    ];
                }
            }
        }

        return $variationIds;
    }
}
