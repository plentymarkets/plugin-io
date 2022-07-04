<?php //strict

namespace IO\Services;

use Illuminate\Database\Eloquent\Collection;
use IO\Builder\Order\AddressType;
use IO\Builder\Order\OrderItemType;
use IO\Builder\Order\OrderType;
use IO\Constants\OrderPaymentStatus;
use IO\Extensions\Constants\ShopUrls;
use IO\Extensions\Filters\PropertyNameFilter;
use IO\Extensions\Mail\SendMail;
use IO\Guards\AuthGuard;
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
use Plenty\Modules\Order\Property\Models\OrderProperty;
use Plenty\Modules\Order\Settings\Contracts\OrderSettingsRepositoryContract;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\GiftCardRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Webshop\Order\Contracts\OrderRepositoryContract as WebshopOrderRepositoryContract;

/**
 * Service Class OrderService
 *
 * This service class contains function related to orders.
 * All public functions are available in the Twig template renderer.
 *
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

    /** @var GiftCardRepositoryContract $giftCardRepository */
    private $giftCardRepository;

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
     * @param GiftCardRepositoryContract $giftCardRepository
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
        ContactRepositoryContract $contactRepository,
        GiftCardRepositoryContract $giftCardRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->basketService = $basketService;
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
        $this->addressRepository = $addressRepository;
        $this->urlService = $urlService;
        $this->checkoutService = $checkoutService;
        $this->customerService = $customerService;
        $this->contactRepository = $contactRepository;
        $this->giftCardRepository = $giftCardRepository;
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
     * @param string $email
     * @param int $billingAddressId
     * @throws \Throwable
     */
    public function subscribeToNewsletter($email, $billingAddressId)
    {
        /** @var CustomerNewsletterService $customerNewsletterService $email */
        $customerNewsletterService = pluginApp(CustomerNewsletterService::class);
        $newsletterSubscriptions = $this->sessionStorageRepository->getSessionValue(
            SessionStorageRepositoryContract::NEWSLETTER_SUBSCRIPTIONS
        );

        if (is_array($newsletterSubscriptions) && count($newsletterSubscriptions) && strlen($email)) {
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
     * @return array An array containing a type ("success"|"error") and a value.
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
     * Find an order by id
     * @param int $orderId An order id to find order by
     * @param bool $wrap Optional: Wrap order into an /IO/Models/LocalizedOrder (Default: true)
     * @return LocalizedOrder|Order
     */
    public function findOrderById(int $orderId, $wrap = true)
    {
        $order = $this->orderRepository->findOrderById($orderId);

        if ($wrap) {
            return LocalizedOrder::wrap($order, Utils::getLang());
        }

        return $order;
    }

    /**
     * Find an order by id and authorize it via accesskey
     * @param int $orderId An order id find order by
     * @param string $orderAccessKey An order access key to authorize search for order
     * @return LocalizedOrder|null
     */
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
                    $backlink = $this->getConfirmationUrl($shopUrls->confirmation, $orderId, $orderAccessKey);
                    AuthGuard::redirect($shopUrls->login . '?backlink=' . $backlink);
                } elseif ((int)$orderContactId !== (int)$this->contactRepository->getContactId()) {
                    return null;
                }
            }
        }
        return LocalizedOrder::wrap($order, Utils::getLang());
    }

    /**
     * Get a confirmation url with correct structure and parameter
     *
     * @param string $confirmationBaseUrl
     * @param int $orderId
     * @param string $orderAccessKey
     *
     * @return string
     */
    public function getConfirmationUrl(string $confirmationBaseUrl, int $orderId, string $orderAccessKey): string
    {
        if (RouteConfig::isActive(RouteConfig::CONFIRMATION)) {
            if (strlen($orderAccessKey) && (int)$orderId > 0) {
                $confirmationBaseUrl .= '/' . $orderId . '/' . $orderAccessKey;
            }
        } elseif (in_array(RouteConfig::CONFIRMATION, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0) {
            if (strlen($orderAccessKey) && (int)$orderId > 0) {
                $confirmationBaseUrl .= '?orderId=' . $orderId . '&accessKey=' . $orderAccessKey;
            }
        }
        return $confirmationBaseUrl;
    }

    /**
     * Get a list of orders for a contact
     * @param int $contactId An contacts id
     * @param int $page Optional: Page number for pagination (Default: 1)
     * @param int $items Optional: How many items per page (Default: 50)
     * @param array $filters Optional: Additional filters for search
     * @param bool $wrapped Optional: Wrap orders /IO/Models/LocalizedOrder instances (Default: true)
     * @return PaginatedResult
     */
    public function getOrdersForContact(
        int $contactId,
        int $page = 1,
        int $items = 50,
        array $filters = [],
        $wrapped = true
    )
    {
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

    /**
     * Get a list of orders for a contact in a compact and reduced format
     * @param int $page Optional: Page for pagination (Default: 1)
     * @param int $items Optional: Number of items per page (Default: 50)
     * @return PaginatedResult|null
     */
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
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);
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
                        'currency' =>  $totals['currency'],
                        'status' => $orderStatusName,
                        'creationDate' => $creationDate,
                        'shippingDate' => $shippingDate,
                        'trackingURL' => $orderTrackingService->getTrackingURL($order, $lang),
                        'confirmationURL' => $shopUrls->orderConfirmation($order->id)
                    ];
                }
            };

            $orderResult->setResult($orders);
        }

        return $orderResult;
    }

    /**
     * Get the last order created by the current contact
     * @param int $contactId A contacts id to find orders for
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
            $latestOrderId = $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::LATEST_ORDER_ID);
            if ($latestOrderId > 0) {
                $order = $this->orderRepository->findOrderById(
                    $latestOrderId
                );
            }
        }

        if (!is_null($order)) {
            return LocalizedOrder::wrap($order, Utils::getLang());
        }

        return null;
    }

    /**
     * Find order properties of a specific type for a specific order
     * @param int $orderId An order id to find order properties for
     * @param int $typeId The type of order properties to find
     * @return Collection|OrderProperty[]
     */
    public function getOrderPropertyByOrderId($orderId, $typeId)
    {
        /**
         * @var OrderPropertyRepositoryContract $orderPropertyRepo
         */
        $orderPropertyRepo = pluginApp(OrderPropertyRepositoryContract::class);
        return $orderPropertyRepo->findByOrderId($orderId, $typeId);
    }

    /**
     * Check if the shop has activated return orders
     * @return bool
     */
    public function isReturnActive()
    {
        return RouteConfig::isActive(RouteConfig::ORDER_RETURN);
    }

    /**
     * Create a return order for a specific order
     * @param int $orderId The order id to create a return order for
     * @param string $orderAccessKey Optional: An order access key is needed for guests
     * @param array $items Optional: Array of items to return
     * @param string $returnNote Optional: A optional reason for returning items
     * @return mixed|Order|null
     * @throws \Throwable
     */
    public function createOrderReturn($orderId, $orderAccessKey = '', $items = [], $returnNote = '')
    {
        $localizedOrder = $this->getReturnOrder($orderId, $orderAccessKey);
        if ($localizedOrder->isReturnable()) {
            $returnOrderData = $localizedOrder->orderData;

            foreach ($returnOrderData['orderItems'] as $i => $orderItem) {
                $variationId = $orderItem['itemVariationId'];
                $returnQuantity = max((int)$items[$variationId], $orderItem->quantity);

                $minQuantityToReturn = $this->giftCardRepository->getReturnQuantity($orderItem['id']);
                if ($minQuantityToReturn > 0 && $returnQuantity !== $minQuantityToReturn) {
                    throw new \Exception("GiftCard is not returnable with this quantity", 502);
                }

                if ($returnQuantity > 0) {
                    $returnOrderData['orderItems'][$i]['quantity'] = $returnQuantity;
                } else {
                    unset($returnOrderData['orderItems'][$i]);
                }
            }

            $returnOrder = [];
            $returnOrder['quantities'] = array_map(function ($returnOrderItem) {
                return [
                    'orderItemId' => $returnOrderItem['id'],
                    'quantity' => $returnOrderItem['quantity']
                ];
            }, $returnOrderData['orderItems']);

            $returnOrder['statusId'] = $this->getReturnOrderStatus();

            if (!is_null($returnNote) && strlen($returnNote)) {
                $returnOrder["comments"][] = [
                    "isVisibleForContact" => true,
                    "text" => $returnNote
                ];
            }

            /** @var WebshopOrderRepositoryContract $webshopOrderRepository */
            $webshopOrderRepository = pluginApp(WebshopOrderRepositoryContract::class);

            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $createdReturn = $authHelper->processUnguarded(
                function () use ($webshopOrderRepository, $returnOrder, $orderId) {
                    return $webshopOrderRepository->createReturnOrder($returnOrder, (int)$orderId);
                }
            );

            return $createdReturn;
        }

        return $localizedOrder->order;
    }

    /**
     * Find a return order by order id
     * @param int $orderId An order id to find return order for
     * @param string $orderAccessKey Optional: An order access key is needed to authorize guests
     * @return LocalizedOrder
     * @throws \Throwable
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

        /** @var PropertyNameFilter $propertyNameFilter */
        $propertyNameFilter = pluginApp(PropertyNameFilter::class);

        foreach ($orderData['orderItems'] as &$orderItem) {
            foreach ($orderItem['orderProperties'] as &$orderProperty) {
                $orderProperty['name'] = $propertyNameFilter->getPropertyName($orderProperty);
                if ($orderProperty->type === 'selection') {
                    $orderProperty->selectionValueName = $propertyNameFilter->getPropertySelectionValueName(
                        $orderProperty
                    );
                }
            }
            unset($orderProperty);
        }
        unset($orderItem);

        $localizedOrder->orderData = $orderData;

        return $localizedOrder;
    }

    /**
     * Get all items of an order that can be returned
     * @param Order $order An order
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
                && !($orderItem->bundleType === 'bundle_item' && count($orderItem->references) > 0)
                && $this->giftCardRepository->isReturnable($orderItem->id)) {
                $orderItemData = $orderItem->toArray();
                $orderItemData['minQuantity'] = $this->giftCardRepository->getReturnQuantity($orderItem->id);
                $orderItemData['quantity'] = $newQuantity;
                $newOrderItems[] = $orderItemData;
            }
        }

        return $newOrderItems;
    }

    /**
     * List all payment methods available for switch in MyAccount
     * @param int $currentPaymentMethodId Optional: The id of the current payment method
     * @param int|null $orderId Optional: An order id to find valid payment methods to switch to
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
     * Check if it is possible to switch to another payment method from a specific one
     * @param int $paymentMethodId A payment method id to check switching from
     * @param int|null $orderId Optional: An order id used for additional checks
     * @return bool
     * @throws \Throwable
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
        return $this->frontendPaymentMethodRepository->getPaymentMethodSwitchableFromById($paymentMethodId, $orderId);
    }


    /**
     * Switch the payment method of an order to a new payment method
     * @param int $orderId An order id to switch payment method for
     * @param int $paymentMethodId A payment method id to switch to
     * @param string $accessKey The access key must be compatible with order id
     *
     * @return LocalizedOrder|null
     * @throws \Throwable
     */
    public function switchPaymentMethodForOrder($orderId, $paymentMethodId, $accessKey)
    {
        /** @var \Plenty\Modules\Webshop\Order\Contracts\OrderRepositoryContract $orderRepostory */
        $orderRepostory = pluginApp(\Plenty\Modules\Webshop\Order\Contracts\OrderRepositoryContract::class);
        $order = $orderRepostory->switchPaymentMethodForOrder($orderId, $paymentMethodId, $accessKey);

        if (!is_null($order)) {
            return LocalizedOrder::wrap($order, Utils::getLang());
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

        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::LATEST_ORDER_ID,
            $order->id
        );
    }

    /**
     * Remove the bundle prefix from the order item name.
     * Default prefix is "[BUNDLE] "
     * 
     * @param string $name
     * @return string
     */
    public function removeBundlePrefix(string $name): string
    {
        return $this->removeOrderItemPrefix($name, \Plenty\Modules\Order\Models\OrderItemType::TYPE_ITEM_BUNDLE);
    }

    /**
     * Remove the bundle component prefix from the order item name.
     * Default prefix is "[-] "
     * 
     * @param string $name
     * @return string
     */
    public function removeBundleComponentPrefix(string $name): string
    {
        return $this->removeOrderItemPrefix($name, \Plenty\Modules\Order\Models\OrderItemType::TYPE_BUNDLE_COMPONENT);
    }

    /**
     * @param string $name
     * @param int $orderItemTypeId
     * @return string
     */
    private function removeOrderItemPrefix(string $name, int $orderItemTypeId): string
    {
        $prefix = $this->getOrderItemPrefix($orderItemTypeId);
        if ($prefix !== null && (substr($name, 0, strlen($prefix)) == $prefix)) {
            $name = substr($name, strlen($prefix));
        }

        return $name;
    }

    /**
     * Check if the prefix for components is included in the name
     *
     * @param string $name
     * @return bool
     */
    public function containsComponentPrefix(string $name): bool
    {
        $prefix = $this->getOrderItemPrefix(\Plenty\Modules\Order\Models\OrderItemType::TYPE_BUNDLE_COMPONENT);
        if ($prefix !== null && (substr($name, 0, strlen($prefix)) == $prefix)) {
            return true;
        }
        return false;
    }

    /**
     * Get prefix for item type id
     *
     * @param int $orderItemTypeId
     * @return int|null
     */
    public function getOrderItemPrefix(int $orderItemTypeId)
    {
        /** @var OrderSettingsRepositoryContract $settingsRepository */
        $settingsRepository = pluginApp(OrderSettingsRepositoryContract::class);
        $settings = $settingsRepository->get();
        return $settings->orderItemPrefixes[$orderItemTypeId] ?? null;
    }

    /**
     * @param \Throwable $throwable
     * @param null $message
     */
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

    /**
     * @return float|mixed
     * @throws \Throwable
     */
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
