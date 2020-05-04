<?php

namespace IO\Models;

use IO\Services\TemplateConfigService;
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
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

class LocalizedOrder extends ModelWrapper
{
    /**
     * The OrderItem types that will be wrapped. All other OrderItems will be stripped from the order.
     */
    const WRAPPED_ORDERITEM_TYPES = [
        OrderItemType::TYPE_VARIATION,
        OrderItemType::TYPE_ITEM_BUNDLE,
        OrderItemType::TYPE_BUNDLE_COMPONENT,
        OrderItemType::TYPE_PROMOTIONAL_COUPON,
        OrderItemType::TYPE_GIFT_CARD,
        OrderItemType::TYPE_SHIPPING_COSTS,
        OrderItemType::TYPE_UNASSIGEND_VARIATION,
        OrderItemType::TYPE_ITEM_SET,
        OrderItemType::TYPE_SET_COMPONENT
    ];
    /**
     * @var Order
     */
    public $order = null;

    public $orderData = [];

    public $status = null;
    public $shippingProvider = "";
    public $shippingProfileName = "";
    public $shippingProfileId = 0;
    public $trackingURL = "";
    public $paymentMethodName = "";
    public $paymentMethodIcon = "";
    public $paymentStatus = '';

    public $variations = [];
    public $itemURLs = [];
    public $itemImages = [];
    public $isReturnable = false;

    public $highlightNetPrices = false;
    public $totals = [];

    public $allowPaymentMethodSwitchFrom = false;
    public $paymentMethodListForSwitch = [];

    /**
     * @param Order $order
     * @param array ...$data
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
        foreach ($order->orderItems as $key => $orderItem) {
            if (in_array((int)$orderItem->typeId, self::WRAPPED_ORDERITEM_TYPES)) {
                if ($orderItem->itemVariationId !== 0) {
                    $orderVariationIds[] = $orderItem->itemVariationId;
                }
            } else {
                unset($order->orderItems[$key]);
            }
        }

        $resultFields = ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_LIST_ITEM);
        foreach (
            [
                'attributes.attribute.names.*',
                'attributes.value.names.*',
                'images.all.urlPreview',
                'images.variation.urlPreview',
                'variationProperties.*'
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

        /** @var OrderTotalsService $orderTotalsService */
        $orderTotalsService = pluginApp(OrderTotalsService::class);
        $instance->highlightNetPrices = $orderTotalsService->highlightNetPrices($instance->order);

        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $order = $this->order->toArray();
        $order['billingAddress'] = $this->order->billingAddress->toArray();
        $order['deliveryAddress'] = $this->order->deliveryAddress->toArray();
        $order['documents'] = $this->order->documents->toArray();

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
     * @param int $setOrderItemId
     * @param OrderItem[] $orderItems
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
