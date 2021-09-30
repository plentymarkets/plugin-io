<?php

namespace IO\Models;

use IO\Builder\Order\OrderType;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Date\Models\OrderDateType;
use Plenty\Modules\Order\Models\OrderItem;
use Plenty\Modules\Order\Models\OrderItemType;
use Plenty\Modules\Webshop\Contracts\GiftCardRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\OrderService;
use IO\Services\OrderStatusService;
use IO\Services\OrderTotalsService;
use IO\Services\OrderTrackingService;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Property\Models\OrderProperty;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use IO\Extensions\Filters\URLFilter;
use IO\Extensions\Filters\ItemImagesFilter;
use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Helpers\VariationPropertyConverter;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

/**
 * Class LocalizedOrder
 *
 * Data representation for an order.
 *
 * @package IO\Models
 */
class LocalizedOrder extends ModelWrapper
{
    /** @var array The OrderItem types that will be wrapped. All other OrderItems will be stripped from the order. */
    const WRAPPED_ORDERITEM_TYPES = [
        OrderItemType::TYPE_VARIATION,
        OrderItemType::TYPE_ITEM_BUNDLE,
        OrderItemType::TYPE_BUNDLE_COMPONENT,
        OrderItemType::TYPE_PROMOTIONAL_COUPON,
        OrderItemType::TYPE_GIFT_CARD,
        OrderItemType::TYPE_SHIPPING_COSTS,
        OrderItemType::TYPE_UNASSIGEND_VARIATION,
        OrderItemType::TYPE_DEPOSIT,
        OrderItemType::TYPE_ITEM_SET,
        OrderItemType::TYPE_SET_COMPONENT,
        OrderItemType::TYPE_ORDER_PROPERTY
    ];

    /** @var array The OrderItem types that will be wrapped. All other OrderItems will be stripped from the return order. */
    const WRAPPED_ORDERITEM_TYPES_FOR_RETURN = [
        OrderItemType::TYPE_VARIATION,
        OrderItemType::TYPE_ITEM_BUNDLE,
        OrderItemType::TYPE_BUNDLE_COMPONENT,
        OrderItemType::TYPE_PROMOTIONAL_COUPON,
        OrderItemType::TYPE_GIFT_CARD,
        OrderItemType::TYPE_UNASSIGEND_VARIATION,
        OrderItemType::TYPE_DEPOSIT,
        OrderItemType::TYPE_ITEM_SET,
        OrderItemType::TYPE_SET_COMPONENT,
        OrderItemType::TYPE_ORDER_PROPERTY
    ];

    /** @var array $order Specific order data. */
    public $order = null;
    /** @var array $orderData Specific order data, should be filled with an return order. */
    public $orderData = [];
    /** @var string $status Name of the order status (example: return). */
    public $status = null;
    /** @var string $shippingProvider Name of the shipping provider (example: DHL). */
    public $shippingProvider = "";
    /** @var string $shippingProfileName Name of the shipping profile. */
    public $shippingProfileName = "";
    /** @var int $shippingProfileId The ID of the shipping profile (Default: 0). */
    public $shippingProfileId = 0;
    /** @var string $trackingURL Tracking URL for the order from shipping provider. */
    public $trackingURL = "";
    /** @var string $paymentMethodName Name of the payment method. */
    public $paymentMethodName = "";
    /** @var string $paymentMethodIcon URL of payment method icon image. */
    public $paymentMethodIcon = "";
    /** @var string $paymentStatus Payment status see IO/Constants/OderPaymentStatus. */
    public $paymentStatus = "";

    /** @var array $variations Item variations and their data inside the order. */
    public $variations = [];
    /** @var array $itemURLs URLs of item variations. */
    public $itemURLs = [];
    /** @var array $itemImages URLs to images of item variations. */
    public $itemImages = [];
    /** @var bool $isReturnable Indicate if order is returnable. */
    public $isReturnable = false;

    /** @var bool $highlightNetPrices Indicate if net prices should be shown/highlighted. */
    public $highlightNetPrices = false;
    /** @var array $totals Totals data of the order. */
    public $totals = [];

    /** @var bool $allowPaymentMethodSwitchFrom Indicate if is possible to switch to another payment method from the chosen one. */
    public $allowPaymentMethodSwitchFrom = false;
    /** @var array $paymentMethodListForSwitch List all payment methods that can be switched to. */
    public $paymentMethodListForSwitch = [];

    /**
     * Data preparation for the specific order and params.
     *
     * @param Order $order Specific order model.
     * @param array ...$data Additional params.
     * @return LocalizedOrder|null
     */
    public static function wrap($order, ...$data)
    {
        if ($order == null) {
            return null;
        }
        /** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository */
        $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);

        /** @var OrderTotalsService $orderTotalsService */
        $orderTotalsService = pluginApp(OrderTotalsService::class);

        /** @var OrderService $orderService */
        $orderService = pluginApp(OrderService::class);

        /** @var URLFilter $urlFilter */
        $urlFilter = pluginApp(URLFilter::class);

        /** @var ItemImagesFilter $imageFilter */
        $imageFilter = pluginApp(ItemImagesFilter::class);

        /** @var OrderStatusService $orderStatusService */
        $orderStatusService = pluginApp(OrderStatusService::class);

        /** @var GiftCardRepositoryContract $giftCardRepository */
        $giftCardRepository = pluginApp(GiftCardRepositoryContract::class);

        list($lang) = $data;

        $instance = pluginApp(self::class);
        $instance->order = $order;
        
        $creationDate = $order->getDate(OrderDateType::ORDER_ENTRY_AT);
        $instance->order->createdAt = $creationDate->date;

        $instance->status = [];
        $instance->totals = $orderTotalsService->getAllTotals($order);


        try {
            $shippingProfile = $parcelServicePresetRepository->getPresetById($order->shippingProfileId);
            $instance->shippingProfileId = (int)$order->shippingProfileId;
            foreach ($shippingProfile->parcelServicePresetNames as $name) {
                if ($name->lang === $lang) {
                    $instance->shippingProfileName = $name->name;
                    break;
                }
            }

            foreach ($shippingProfile->parcelServiceNames as $name) {
                if ($name->lang === $lang) {
                    $instance->shippingProvider = $name->name;
                    break;
                }
            }

            /** @var OrderTrackingService $orderTrackingService */
            $orderTrackingService = pluginApp(OrderTrackingService::class);
            $instance->trackingURL = $orderTrackingService->getTrackingURL($order, $lang);
        } catch (\Exception $e) {
        }

        $frontentPaymentRepository = pluginApp(FrontendPaymentMethodRepositoryContract::class);

        try {
            $instance->paymentMethodName = $frontentPaymentRepository->getPaymentMethodNameById(
                $order->methodOfPaymentId,
                $lang
            );
            $instance->paymentMethodIcon = $frontentPaymentRepository->getPaymentMethodIconById(
                $order->methodOfPaymentId,
                $lang
            );
        } catch (\Exception $e) {
        }

        $paymentStatusProperty = $order->properties->firstWhere('typeId', OrderPropertyType::PAYMENT_STATUS);
        if ($paymentStatusProperty instanceof OrderProperty) {
            $instance->paymentStatus = $paymentStatusProperty->value;
        }

        $paymentMethodIdProperty = $order->properties->firstWhere('typeId', OrderPropertyType::PAYMENT_METHOD);
        if ($paymentMethodIdProperty instanceof OrderProperty) {
            $instance->allowPaymentMethodSwitchFrom = $orderService->allowPaymentMethodSwitchFrom(
                $paymentMethodIdProperty->value,
                $order->id
            );
            $instance->paymentMethodListForSwitch = $orderService->getPaymentMethodListForSwitch(
                $paymentMethodIdProperty->value,
                $order->id
            );
        }

        $instance->status = $orderStatusService->getOrderStatus($order->id, $order->statusId);

        $orderVariationIds = [];

        $wrappedOrderitemTypes = $order->typeId === OrderType::RETURNS ? self::WRAPPED_ORDERITEM_TYPES_FOR_RETURN : self::WRAPPED_ORDERITEM_TYPES;
        foreach ($order->orderItems as $key => $orderItem) {
            if (in_array((int)$orderItem->typeId, $wrappedOrderitemTypes)) {
                if ($orderItem->itemVariationId !== 0) {
                    $orderVariationIds[] = $orderItem->itemVariationId;
                }
            } else {
                unset($order->orderItems[$key]);
            }
        }

        $resultFields = ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_LIST_ITEM);
        /**
         * TODO replace with order item template in upcoming version
         */
        foreach (
            [
                'attributes.attribute.names.*',
                'attributes.value.names.*',
                'images.all.urlPreview',
                'images.variation.urlPreview',
                'variationProperties.*',
                'variation.number',
                'texts.description',
                'texts.shortDescription'
            ] as $field
        ) {
            if (!in_array($field, $resultFields)) {
                $resultFields[] = $field;
            }
        }

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp(VariationSearchFactory::class);
        $searchFactory->setPage(1, count($orderVariationIds));
        $orderVariations = $itemSearchService->getResults(
            [
                $searchFactory
                    ->withLanguage()
                    ->withImages()
                    ->withPropertyGroups(['displayInOrderProcess'])
                    ->withDefaultImage()
                    ->withUrls()
                    ->withBundleComponents()
                    ->withResultFields(
                        $resultFields
                    )
                    ->hasVariationIds($orderVariationIds)
            ]
        )[0];

        foreach ($orderVariations['documents'] as $orderVariation) {
            $variationId = $orderVariation['data']['variation']['id'];
            $instance->variations[$variationId] = $orderVariation['data'];
            $instance->itemURLs[$variationId] = $urlFilter->buildItemURL($orderVariation['data']);
            $instance->itemImages[$variationId] = $imageFilter->getFirstItemImageUrl(
                $orderVariation['data']['images'],
                'urlPreview'
            );

            foreach ($instance->order->relations['orderItems'] as $orderItem) {
                if ($orderItem['itemVariationId'] == $orderVariation['data']['variation']['id']) {
                    $orderItem['bundleComponents'] = $orderVariation['data']['bundleComponents'];
                    $orderItem['bundleType'] = $orderVariation['data']['variation']['bundleType'];

                    $giftCardInformation = $giftCardRepository->getGiftCardInformation($orderItem['id']);
                    $giftItem = [];
                    $giftItem['isGiftCard'] = count($giftCardInformation) ? true : false;
                    $giftItem['information'] = $giftCardInformation;
                    $giftItem['hasPdf'] = count($giftCardInformation) ? $giftCardRepository->hasGiftCardPdf(
                        $order->id,
                        $orderItem['id'],
                        $giftCardInformation[0]['id']
                    ) : false;

                    $orderItem['giftCard'] = $giftItem;
                    $attributes = [];

                    foreach ($orderVariation['data']['attributes'] as $attribute) {
                        $attributes[] = [
                            'name' => $attribute['attribute']['names']['name'],
                            'value' => $attribute['value']['names']['name']
                        ];
                    }

                    $orderItem['attributes'] = $attributes;
                    $orderItem['variationProperties'] = $orderVariation['data']['variationProperties'];
                }
            }
        }

        $setComponentKeys = [];
        foreach ($instance->order->relations['orderItems'] as $key => $orderItem) {
            if ($orderItem->typeId === OrderItemType::TYPE_ITEM_SET) {
                $instance->order->orderItems[$key]['setComponents'] = array_values(
                    self::filterSetComponents(
                        $orderItem->id,
                        $instance->order->relations['orderItems']
                    )->toArray()
                );
            } elseif ($orderItem->typeId === OrderItemType::TYPE_SET_COMPONENT) {
                $setComponentKeys[] = $key;
            }
        }

        foreach ($setComponentKeys as $setComponentKey) {
            unset($instance->order->relations['orderItems'][$setComponentKey]);
            unset($instance->order->orderItems[$setComponentKey]);
        }

        /** @var VariationPropertyConverter $variationPropertyConverter */
        $variationPropertyConverter = pluginApp(VariationPropertyConverter::class);
        $instance->order->relations['orderItems'] = $variationPropertyConverter->convertVariationPropertyOrderItems(
            $instance->order
        );

        /** @var OrderTotalsService $orderTotalsService */
        $orderTotalsService = pluginApp(OrderTotalsService::class);
        $instance->highlightNetPrices = $orderTotalsService->highlightNetPrices($instance->order);

        return $instance;
    }

    /**
     * Get an array for the current instance.
     *
     * @return array
     */
    public function toArray(): array
    {
        $order = $this->order->toArray();
        $order['billingAddress'] = $this->order->billingAddress->toArray();
        $order['deliveryAddress'] = $this->order->deliveryAddress->toArray();
        $order['documents'] = $this->order->documents->toArray();
        $order['accessKey'] = $this->getAccessKey($this->order->id);
        if (count($this->orderData)) {
            $order = $this->orderData;
        }
        $data = [
            "order" => $order,
            "status" => $this->status,
            "totals" => $this->totals,
            "shippingProfileId" => $this->shippingProfileId,
            "shippingProvider" => $this->shippingProvider,
            "shippingProfileName" => $this->shippingProfileName,
            "paymentMethodName" => $this->paymentMethodName,
            "paymentMethodIcon" => $this->paymentMethodIcon,
            "paymentStatus" => $this->paymentStatus,
            "allowPaymentMethodSwitchFrom" => $this->allowPaymentMethodSwitchFrom,
            "paymentMethodListForSwitch" => $this->paymentMethodListForSwitch,
            "itemURLs" => $this->itemURLs,
            "itemImages" => $this->itemImages,
            "variations" => $this->variations,
            "isReturnable" => $this->isReturnable(),
            "highlightNetPrices" => $this->highlightNetPrices
        ];

        return $data;
    }


    private function getAccessKey($orderId) {
        /** @var OrderRepositoryContract $orderRepository */
        $orderRepository = pluginApp(OrderRepositoryContract::class);
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        return $authHelper->processUnguarded(
            function () use ($orderId, $orderRepository) {
                return $orderRepository->generateAccessKey($orderId);
            }
        );

    }

    /**
     * Checks if order is returnable.
     *
     * @return bool
     */
    public function isReturnable()
    {
        $order = $this->order->toArray();

        if (in_array($order['typeId'], OrderService::VISIBLE_ORDER_TYPES)) {
            $orderItems = count($this->orderData)
                ? $this->orderData['orderItems']
                : $order['orderItems'];

            if (!count($orderItems)) {
                return false;
            }

            /** @var OrderService $orderService */
            $orderService = pluginApp(OrderService::class);
            $returnableItems = $orderService->getReturnableItems($this->order);
            if (!count($returnableItems)) {
                return false;
            }

            $shippingDateSet = false;
            $createdDateUnix = 0;

            $dates = count($this->orderData) ? $this->orderData['dates'] : $order['dates'];
            foreach ($dates as $date) {
                if ($date['typeId'] === 5 && strlen($date['date'])) {
                    $shippingDateSet = true;
                } elseif ($date['typeId'] === 2 && strlen($date['date'])) {
                    $createdDateUnix = strtotime($date['date']);
                }
            }

            /**  @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $returnTime = $templateConfigService->getInteger('my_account.order_return_days', 14);

            return $shippingDateSet
                && $createdDateUnix > 0
                && $returnTime > 0
                && time() < $createdDateUnix + ($returnTime * 24 * 60 * 60);
        }

        return false;
    }

    /**
     * Get all set components from order items for a specific order item ID.
     *
     * @param int $setOrderItemId ID of set item
     * @param OrderItem[] $orderItems List of items of the order
     * @return array
     */
    private static function filterSetComponents($setOrderItemId, $orderItems)
    {
        return $orderItems->filter(
            function ($oItem) use ($setOrderItemId) {
                /** @var OrderItem $oItem */
                return $oItem->references
                        ->where('referenceType', 'set')
                        ->where('referenceOrderItemId', $setOrderItemId)
                        ->count() > 0;
            }
        );
    }
}
