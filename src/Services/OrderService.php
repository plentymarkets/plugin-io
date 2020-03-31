<?php //strict

namespace IO\Services;

use IO\Builder\Order\AddressType;
use IO\Builder\Order\OrderItemType;
use IO\Builder\Order\OrderType;
use IO\Constants\OrderPaymentStatus;
use IO\Extensions\Constants\ShopUrls;
use IO\Extensions\Mail\SendMail;
use IO\Helper\RouteConfig;
use IO\Helper\Utils;
use IO\Models\LocalizedOrder;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailOrder;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailTemplate;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Date\Models\OrderDate;
use Plenty\Modules\Order\Date\Models\OrderDateType;
use Plenty\Modules\Order\Models\OrderReference;
use Plenty\Modules\Order\Property\Contracts\OrderPropertyRepositoryContract;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Webshop\Order\Contracts\OrderRepositoryContract as WebshopOrderRepositoryContract;

/**
 * Class OrderService
 * @package IO\Services
 */
class OrderService
{
    use SendMail;
    use Loggable;

    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;
    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var SessionStorageRepositoryContract
     */
    private $sessionStorageRepository;

    /**
     * @var FrontendPaymentMethodRepositoryContract
     */
    private $frontendPaymentMethodRepository;
    /**
     * @var AddressRepositoryContract
     */
    private $addressRepository;
    /**
     * @var UrlService
     */
    private $urlService;

    /** @var CheckoutService $checkoutService */
    private $checkoutService;

    /** @var CustomerService $customerService */
    private $customerService;

    /** @var ContactRepositoryContract $contactRepository */
    private $contactRepository;

    /**
     * The OrderItem types that will be wrapped. All other OrderItems will be stripped from the order.
     */
    const WRAPPED_ORDERITEM_TYPES =
        [
            OrderItemType::VARIATION,
            OrderItemType::ITEM_BUNDLE,
            OrderItemType::BUNDLE_COMPONENT,
            OrderItemType::UNASSIGNED_VARIATION,
            OrderItemType::TYPE_ITEM_SET
        ];

    /**
     * The default visible order types
     */
    const VISIBLE_ORDER_TYPES = [
        OrderType::ORDER,
        OrderType::WARRANTY
    ];

    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     * @param BasketService $basketService
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
     * @param AddressRepositoryContract $addressRepository
     * @param UrlService $urlService
     * @param CheckoutService $checkoutService
     * @param CustomerService $customerService
     * @param ContactRepositoryContract $contactRepository
     */
    public function __construct(
        OrderRepositoryContract $orderRepository,
        BasketService $basketService,
        SessionStorageRepositoryContract $sessionStorageRepository,
        FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository,
        AddressRepositoryContract $addressRepository,
        UrlService $urlService,
        CheckoutService $checkoutService,
        CustomerService $customerService,
        ContactRepositoryContract $contactRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->basketService = $basketService;
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
        $this->addressRepository = $addressRepository;
        $this->urlService = $urlService;
        $this->checkoutService = $checkoutService;
        $this->customerService = $customerService;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Place an order
     * @return LocalizedOrder
     */
    public function placeOrder(): LocalizedOrder
    {
        /** @var WebshopOrderRepositoryContract $webshopOrderRepository */
        $webshopOrderRepository = pluginApp(WebshopOrderRepositoryContract::class);
        $order = $webshopOrderRepository->placeOrder();

        return LocalizedOrder::wrap($order, Utils::getLang());
    }

    /**
     * Subscribe the customer to the newsletter, if stored in the session
     *
     * @param $email
     * @param $billingAddressId
     */
    public function subscribeToNewsletter($email, $billingAddressId)
    {
        /** @var CustomerNewsletterService $customerNewsletterService $email */
        $customerNewsletterService = pluginApp(CustomerNewsletterService::class);
        $newsletterSubscriptions = $this->sessionStorageRepository->getSessionValue(
            SessionStorageRepositoryContract::NEWSLETTER_SUBSCRIPTIONS
        );

        if (count($newsletterSubscriptions) && strlen($email)) {
            $firstName = '';
            $lastName = '';

            $address = $this->customerService->getAddress($billingAddressId, AddressType::BILLING);

            // if the address is for a company, the contact person will be store into the last name
            if (strlen($address->name1)) {
                foreach ($address->options as $option) {
                    if ($option['typeId'] === AddressOption::TYPE_CONTACT_PERSON) {
                        $lastName = $option['value'];

                        break;
                    }
                }
            } else {
                $firstName = $address->name2;
                $lastName = $address->name3;
            }

            $customerNewsletterService->saveMultipleNewsletterData(
                $email,
                $newsletterSubscriptions,
                $firstName,
                $lastName
            );
        }

        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::NEWSLETTER_SUBSCRIPTIONS,
            null
        );
    }

    /**
     * Execute the payment for a given order.
     * @param int $orderId The order id to execute payment for
     * @param int $paymentId The MoP-ID to execute
     * @return array            An array containing a type ("succes"|"error") and a value.
     */
    public function executePayment(int $orderId, int $paymentId): array
    {
        $result = [];
        try {
            $paymentRepository = pluginApp(PaymentMethodRepositoryContract::class);
            $result = $paymentRepository->executePayment($paymentId, $orderId);
        } catch (\Exception $e) {
            $this->getLogger(__CLASS__)->error(
                'IO::Debug.OrderService_executePaymentFailed',
                [
                    'orderId' => $orderId,
                    'paymentId' => $paymentId,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
        }

        if ($result['type'] === "error") {
            $this->getLogger(__CLASS__)->error(
                'IO::Debug.OrderService_executePaymentError',
                [
                    'orderId' => $orderId,
                    'paymentId' => $paymentId,
                    'response' => $result
                ]
            );
        } else {
            $this->getLogger(__CLASS__)->debug(
                'IO::Debug.OrderService_executePayment',
                [
                    'orderId' => $orderId,
                    'paymentId' => $paymentId,
                    'response' => $result
                ]
            );
        }

        return $result;
    }

    /**
     * Find an order by ID
     * @param int $orderId
     * @param bool $wrap
     * @return LocalizedOrder|mixed|Order
     */
    public function findOrderById(int $orderId, $wrap = true)
    {
        $order = $this->orderRepository->findOrderById($orderId);

        if ($wrap) {
            return LocalizedOrder::wrap($order, Utils::getLang());
        }

        return $order;
    }

    public function findOrderByAccessKey($orderId, $orderAccessKey)
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $redirectToLogin = $templateConfigService->getBoolean('my_account.confirmation_link_login_redirect');

        $order = $this->orderRepository->findOrderByAccessKey($orderId, $orderAccessKey);

        if ($redirectToLogin) {
            $orderContactId = 0;
            foreach ($order->relations as $relation) {
                if ($relation['referenceType'] == 'contact' && (int)$relation['referenceId'] > 0) {
                    $orderContactId = $relation['referenceId'];
                }
            }

            if ((int)$orderContactId > 0) {
                if ((int)$this->contactRepository->getContactId() <= 0) {
                    /** @var ShopUrls $shopUrls */
                    $shopUrls = pluginApp(ShopUrls::class);
                    return $this->urlService->redirectTo(
                        $shopUrls->login . '?backlink=' . $shopUrls->confirmation . '/' . $orderId . '/' . $orderAccessKey
                    );
                } elseif ((int)$orderContactId !== (int)$this->contactRepository->getContactId()) {
                    return null;
                }
            }
        }
        return LocalizedOrder::wrap($order, Utils::getLang());
    }

    /**
     * Get a list of orders for a contact
     * @param int $contactId
     * @param int $page
     * @param int $items
     * @param array $filters
     * @param bool $wrapped
     * @return PaginatedResult
     */
    public function getOrdersForContact(
        int $contactId,
        int $page = 1,
        int $items = 50,
        array $filters = [],
        $wrapped = true
    ) {
        if (!isset($filters['orderType']) && !isset($filters['orderTypes'])) {
            $filters['orderTypes'] = self::VISIBLE_ORDER_TYPES;
        }

        $this->orderRepository->setFilters($filters);

        $orders = $this->orderRepository->allOrdersByContact(
            $contactId,
            $page,
            $items
        );

        if ($wrapped) {
            $orders = LocalizedOrder::wrapPaginated($orders, Utils::getLang());
        }

        return $orders;
    }

    public function getOrdersCompact(int $page = 1, int $items = 50)
    {
        $orderResult = null;
        $contactId = $this->contactRepository->getContactId();

        if ($contactId > 0) {
            $this->orderRepository->setFilters(['orderTypes' => self::VISIBLE_ORDER_TYPES]);

            /** @var PaginatedResult $orderResult */
            $orderResult = $this->orderRepository->allOrdersByContact(
                $contactId,
                $page,
                $items
            );

            /** @var OrderTotalsService $orderTotalsService */
            $orderTotalsService = pluginApp(OrderTotalsService::class);

            /** @var OrderStatusService $orderStatusService */
            $orderStatusService = pluginApp(OrderStatusService::class);

            /** @var OrderTrackingService $orderTrackingService */
            $orderTrackingService = pluginApp(OrderTrackingService::class);

            $lang = Utils::getLang();

            $orders = [];
            foreach ($orderResult->getResult() as $order) {
                if ($order instanceof Order) {
                    $totals = $orderTotalsService->getAllTotals($order);
                    $highlightNetPrices = $orderTotalsService->highlightNetPrices($order);

                    $orderStatusName = $orderStatusService->getOrderStatus($order->id, $order->statusId);

                    $creationDate = '';
                    $creationDateData = $order->dates->firstWhere('typeId', OrderDateType::ORDER_ENTRY_AT);

                    if ($creationDateData instanceof OrderDate) {
                        $creationDate = $creationDateData->date->toDateTimeString();
                    }

                    $shippingDate = '';
                    $shippingDateData = $order->dates->firstWhere('typeId', OrderDateType::ORDER_COMPLETED_ON);

                    if ($shippingDateData instanceof OrderDate) {
                        $shippingDate = $shippingDateData->date->toDateTimeString();
                    }

                    $orders[] = [
                        'id' => $order->id,
                        'parentOrderId' => $order->parentOrder->id,
                        'type' => $order->typeId,
                        'total' => $highlightNetPrices ? $totals['totalNet'] : $totals['totalGross'],
                        'status' => $orderStatusName,
                        'creationDate' => $creationDate,
                        'shippingDate' => $shippingDate,
                        'trackingURL' => $orderTrackingService->getTrackingURL($order, $lang)
                    ];
                }
            };

            $orderResult->setResult($orders);
        }

        return $orderResult;
    }

    /**
     * Get the last order created by the current contact
     * @param int $contactId
     * @return LocalizedOrder|null
     */
    public function getLatestOrderForContact(int $contactId)
    {
        if ($contactId > 0) {
            $order = $this->orderRepository->getLatestOrderByContactId($contactId);

            // load parent order, if given
            if ($order->typeId !== OrderType::ORDER) {
                foreach ($order->orderReferences as $relation) {
                    if ($relation->referenceType === OrderReference::REFERENCE_TYPE_PARENT) {
                        $order = $relation->referenceOrder;
                        break;
                    }
                }
            }
        } else {
            $order = $this->orderRepository->findOrderById(
                $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::LATEST_ORDER_ID)
            );
        }

        if (!is_null($order)) {
            return LocalizedOrder::wrap($order, Utils::getLang());
        }

        return null;
    }

    public function getOrderPropertyByOrderId($orderId, $typeId)
    {
        /**
         * @var OrderPropertyRepositoryContract $orderPropertyRepo
         */
        $orderPropertyRepo = pluginApp(OrderPropertyRepositoryContract::class);
        return $orderPropertyRepo->findByOrderId($orderId, $typeId);
    }

    public function isReturnActive()
    {
        return RouteConfig::isActive(RouteConfig::ORDER_RETURN);
    }

    public function createOrderReturn($orderId, $orderAccessKey = '', $items = [], $returnNote = '')
    {
        $localizedOrder = $this->getReturnOrder($orderId, $orderAccessKey);
        if ($localizedOrder->isReturnable()) {
            $returnOrderData = $localizedOrder->orderData;

            foreach ($returnOrderData['orderItems'] as $i => $orderItem) {
                $variationId = $orderItem['itemVariationId'];
                $returnQuantity = max((int)$items[$variationId], $orderItem->quantity);

                if ($returnQuantity > 0) {
                    $returnOrderData['orderItems'][$i]['quantity'] = $returnQuantity;
                    $returnOrderData['orderItems'][$i]['references'] = [];
                    $returnOrderData['orderItems'][$i]['references'][] = [
                        'referenceOrderItemId' => $orderItem['id'],
                        'referenceType' => 'parent'
                    ];

                    if($orderItem['typeId'] === OrderItemType::TYPE_ITEM_SET && count($orderItem['setComponents']) > 0) {
                        $returnOrderData['orderItems'][$i]['metaData'] = [
                            'externalHash' => 'setItem_' . $orderItem['id']
                        ];

                        foreach($orderItem['setComponents'] as $setComponentOrderItem) {
                            $setComponentOrderItem['metaData'] = [
                                'externalHash' => 'setComponent_' . $setComponentOrderItem['id'],
                                'internalReferences' => [
                                    'set' => 'setItem_' . $orderItem['id']
                                ]
                            ];
                            $setComponentOrderItem['references'] = [];
                            $setComponentOrderItem['references'][] = [
                                'referenceOrderItemId' => $setComponentOrderItem['id'],
                                'referenceType' => 'parent'
                            ];

                            unset($setComponentOrderItem['id']);
                            unset($setComponentOrderItem['orderId']);
                            $returnOrderData['orderItems'][] = $setComponentOrderItem;
                        }
                    }

                    unset($returnOrderData['orderItems'][$i]['id']);
                    unset($returnOrderData['orderItems'][$i]['orderId']);
                } else {
                    unset($returnOrderData['orderItems'][$i]);
                }
            }

            $returnStatus = $this->getReturnOrderStatus();

            $returnOrderData['properties'][] = [
                "typeId" => OrderPropertyType::NEW_RETURNS_MY_ACCOUNT,
                "value" => "1"
            ];
            $returnOrderData['statusId'] = (float)$returnStatus;
            $returnOrderData['typeId'] = OrderType::RETURNS;
            $returnOrderData['orderReferences'] = [];
            $returnOrderData['orderReferences'][] = [
                'referenceOrderId' => $localizedOrder->order->id,
                'referenceType' => 'parent'
            ];

            unset($returnOrderData['id']);
            unset($returnOrderData['dates']);
            unset($returnOrderData['lockStatus']);

            if (!is_null($returnNote) && strlen($returnNote)) {
                $returnOrderData["comments"][] = [
                    "isVisibleForContact" => true,
                    "text" => $returnNote
                ];
            }


            /** @var WebshopOrderRepositoryContract $webshopOrderRepository */
            $webshopOrderRepository = pluginApp(WebshopOrderRepositoryContract::class);

            if (strlen($orderAccessKey)) {
                /** @var AuthHelper $authHelper */
                $authHelper = pluginApp(AuthHelper::class);
                $createdReturn = $authHelper->processUnguarded(
                    function () use ($webshopOrderRepository, $returnOrderData) {
                        $webshopOrderRepository->createReturnOrder($returnOrderData);
                    }
                );
            } else {
                $createdReturn = $webshopOrderRepository->createReturnOrder($returnOrderData);
            }


            return $createdReturn;
        }

        return $localizedOrder->order;
    }

    /**
     * @param $orderId
     * @param string $orderAccessKey
     * @return LocalizedOrder
     */
    public function getReturnOrder($orderId, $orderAccessKey = '')
    {
        $localizedOrder = strlen($orderAccessKey)
            ? $this->findOrderByAccessKey($orderId, $orderAccessKey)
            : $this->findOrderById($orderId);

        /** @var Order $order */
        $order = $localizedOrder->order;

        $orderData = $order->toArray();
        $orderData['orderItems'] = $this->getReturnableItems($order);
        $localizedOrder->orderData = $orderData;

        return $localizedOrder;
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Throwable
     */
    public function getReturnableItems($order)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        // collect quantities of already returned variations
        $returnItems = $authHelper->processUnguarded(
            function () use ($order) {
                $returnItems = [];
                foreach ($order->childOrders as $childOrder) {
                    if ($childOrder->typeId === OrderType::RETURNS) {
                        foreach ($childOrder->orderItems as $orderItem) {
                            $variationId = $orderItem->itemVariationId;
                            $returnItems[$variationId] += $orderItem->quantity;
                        }
                    }
                }
                return $returnItems;
            }
        );

        $newOrderItems = [];
        foreach ($order->orderItems as $key => $orderItem) {
            $newQuantity = $orderItem->quantity;
            if (array_key_exists($orderItem->itemVariationId, $returnItems)) {
                $newQuantity -= $returnItems[$orderItem->itemVariationId];
            }

            if ($newQuantity > 0
                && in_array($orderItem->typeId, self::WRAPPED_ORDERITEM_TYPES)
                && !($orderItem->bundleType === 'bundle_item' && count($orderItem->references) > 0)) {
                $orderItemData = $orderItem->toArray();
                $orderItemData['quantity'] = $newQuantity;
                $newOrderItems[] = $orderItemData;
            }
        }

        return $newOrderItems;
    }

    /**
     * List all payment methods available for switch in MyAccount
     *
     * @param int $currentPaymentMethodId
     * @param null $orderId
     * @return \Illuminate\Support\Collection
     */
    public function getPaymentMethodListForSwitch($currentPaymentMethodId = 0, $orderId = null)
    {
        return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsListForSwitch(
            $currentPaymentMethodId,
            $orderId,
            Utils::getLang()
        );
    }

    /**
     * @param $paymentMethodId
     * @param int $orderId
     * @return bool
     */
    public function allowPaymentMethodSwitchFrom($paymentMethodId, $orderId = null)
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        if (!$templateConfigService->getBoolean('my_account.change_payment')) {
            return false;
        }
        if ($orderId != null) {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $orderRepo = $this->orderRepository;

            $order = $authHelper->processUnguarded(
                function () use ($orderId, $orderRepo) {
                    return $orderRepo->findOrderById($orderId);
                }
            );

            if ($order->paymentStatus !== OrderPaymentStatus::UNPAID) {
                // order was paid
                return false;
            }

            $statusId = $order->statusId;
            $orderCreatedDate = $order->createdAt;

            if (!($statusId <= 3.4 || ($statusId == 5 && $orderCreatedDate->toDateString() == date('Y-m-d')))) {
                return false;
            }
        }
        return $this->frontendPaymentMethodRepository->getPaymentMethodSwitchFromById($paymentMethodId, $orderId);
    }


    /**
     * @param $orderId
     * @param $paymentMethodId
     * @return LocalizedOrder|null
     */
    public function switchPaymentMethodForOrder($orderId, $paymentMethodId)
    {
        if ((int)$orderId > 0) {
            $currentPaymentMethodId = 0;

            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $orderRepo = $this->orderRepository;

            $order = $authHelper->processUnguarded(
                function () use ($orderId, $orderRepo) {
                    return $orderRepo->findOrderById($orderId);
                }
            );

            $newOrderProperties = [];
            $orderProperties = $order->properties;

            if (count($orderProperties)) {
                foreach ($orderProperties as $key => $orderProperty) {
                    $newOrderProperties[$key] = [
                        'typeId' => $orderProperty->typeId,
                        'value' => (string)$orderProperty->value
                    ];
                    if ($orderProperty->typeId == OrderPropertyType::PAYMENT_METHOD) {
                        $currentPaymentMethodId = (int)$orderProperty->value;
                        $newOrderProperties[$key]['value'] = (string)$paymentMethodId;
                    }
                }
            }

            if ($paymentMethodId !== $currentPaymentMethodId) {
                if ($this->frontendPaymentMethodRepository->getPaymentMethodSwitchableFromById(
                        $currentPaymentMethodId,
                        $orderId
                    ) && $this->frontendPaymentMethodRepository->getPaymentMethodSwitchableToById($paymentMethodId)) {
                    $order = $authHelper->processUnguarded(
                        function () use ($orderId, $newOrderProperties, $orderRepo) {
                            return $orderRepo->updateOrder(['properties' => $newOrderProperties], $orderId);
                        }
                    );

                    if (!is_null($order)) {
                        return LocalizedOrder::wrap($order, Utils::getLang());
                    }
                }
            }
        }

        return null;
    }


    /**
     * Do steps after creating the order
     *
     * @param Order $order
     */
    public function complete($order)
    {
        if ($order instanceof Order && $order->id > 0) {
            try {
                $params = [
                    'orderId' => $order->id,
                    'webstoreId' => Utils::getWebstoreId(),
                    'language' => Utils::getLang()
                ];
                $this->sendMail(AutomaticEmailTemplate::SHOP_ORDER, AutomaticEmailOrder::class, $params);
            } catch (\Throwable $throwable) {
                $this->handleThrowable($throwable, "IO::Debug.OrderService_orderCompleteErrorSendMail");
            }

            try {
                if (($order->amounts[0]->invoiceTotal == 0) || ($order->amounts[0]->invoiceTotal == $order->amounts[0]->giftCardAmount)) {
                    $this->createAndAssignDummyPayment($order);
                }
            } catch (\Throwable $throwable) {
                $this->handleThrowable($throwable, "IO::Debug.OrderService_orderCompleteErrorDummyPayment");
            }
        }


        try {
            $email = $this->customerService->getEmail();
            $billingAddressId = $this->checkoutService->getBillingAddressId();
            $this->subscribeToNewsletter($email, $billingAddressId);
        } catch (\Throwable $throwable) {
            $this->handleThrowable($throwable, "IO::Debug.OrderService_orderCompleteErrorSubscribeNewsletter");
        }

        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::ORDER_CONTACT_WISH, null);
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::ORDER_CUSTOMER_SIGN, null);
        if ($this->contactRepository->getContactId() <= 0) {
            $this->sessionStorageRepository->setSessionValue(
                SessionStorageRepositoryContract::LATEST_ORDER_ID,
                $order->id
            );
        }
    }

    private function handleThrowable(\Throwable $throwable, $message = null)
    {
        $this->getLogger(__CLASS__)->error(
            $message ?? "IO::Debug.OrderService_orderCompleteError",
            [
                'message' => $throwable->getMessage()
            ]
        );
    }

    /**
     * Creates a payment with amount 0 and assigns it to the given order so that the status of the given order with amount 0 is calculated correctly.
     * @param Order $order
     */
    private function createAndAssignDummyPayment(Order $order)
    {
        /** @var \Plenty\Modules\Payment\Models\Payment $payment */
        $payment = pluginApp(\Plenty\Modules\Payment\Models\Payment::class);

        $payment->mopId = 5000; // PLENTY_MOP_MANUAL
        $payment->transactionType = \Plenty\Modules\Payment\Models\Payment::TRANSACTION_TYPE_BOOKED_POSTING;
        $payment->status = \Plenty\Modules\Payment\Models\Payment::STATUS_APPROVED;

        /** @var \Plenty\Modules\Order\Models\OrderAmount $orderAmount */
        $orderAmount = $order->amounts->where('isSystemCurrency', false)->first();
        if (!$orderAmount) {
            /** @var \Plenty\Modules\Order\Models\OrderAmount $orderAmount */
            $orderAmount = $order->amounts->where('isSystemCurrency', true)->first();
        }

        $payment->currency = $orderAmount->currency;
        $payment->amount = 0;

        $paymentProperties = [];
        $paymentProperties[] = $this->getPaymentProperty(
            \Plenty\Modules\Payment\Models\PaymentProperty::TYPE_BOOKING_TEXT,
            'ORDER ' . $order->id
        );
        $paymentProperties[] = $this->getPaymentProperty(
            \Plenty\Modules\Payment\Models\PaymentProperty::TYPE_TRANSACTION_ID,
            time()
        );

        $payment->properties = $paymentProperties;
        $payment->regenerateHash = true;

        /** @var PaymentRepositoryContract $paymentRepo */
        $paymentRepo = pluginApp(PaymentRepositoryContract::class);
        $payment = $paymentRepo->createPayment($payment);

        /** @var \Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo */
        $paymentOrderRelationRepo = pluginApp(
            \Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract::class
        );
        $paymentOrderRelationRepo->createOrderRelation($payment, $order);
    }

    /**
     * Returns a PaymentProperty with the given params
     *
     * @param int $typeId
     * @param string $value
     *
     * @return \Plenty\Modules\Payment\Models\PaymentProperty PaymentProperty
     */
    private function getPaymentProperty(int $typeId, string $value)
    {
        /** @var \Plenty\Modules\Payment\Models\PaymentProperty $paymentProperty */
        $paymentProperty = pluginApp(\Plenty\Modules\Payment\Models\PaymentProperty::class);

        $paymentProperty->typeId = $typeId;
        $paymentProperty->value = $value;

        return $paymentProperty;
    }

    private function getReturnOrderStatus()
    {
        $returnStatus = Utils::getTemplateConfig('my_account.order_return_initial_status', 9.0);

        if (strlen($returnStatus) && (float)$returnStatus > 0) {
            $returnStatus = (float)$returnStatus;
            try {
                /** @var AuthHelper $authHelper */
                $authHelper = pluginApp(AuthHelper::class);

                return $authHelper->processUnguarded(
                    function () use ($returnStatus) {
                        $orderStatusRepository = pluginApp(OrderStatusRepositoryContract::class);
                        $status = $orderStatusRepository->get($returnStatus);
                        if (is_null($status)) {
                            return 9.0;
                        }

                        return $returnStatus;
                    }
                );
            } catch (\Exception $e) {
                $this->getLogger(__CLASS__)->warning(
                    'IO::Debug.OrderService_returnStatusNotFound',
                    [
                        'statusId' => $returnStatus
                    ]
                );
            }
        }
        return 9.0;
    }
}
