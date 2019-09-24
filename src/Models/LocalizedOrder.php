<?php

namespace IO\Models;

use IO\Builder\Order\OrderItemType;
use IO\Builder\Order\OrderType;
use IO\Extensions\Filters\ItemImagesFilter;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\OrderService;
use IO\Services\OrderStatusService;
use IO\Services\OrderTotalsService;
use IO\Services\OrderTrackingService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Property\Models\OrderProperty;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use IO\Extensions\Filters\URLFilter;

class LocalizedOrder extends ModelWrapper
{
    /**
     * The OrderItem types that will be wrapped. All other OrderItems will be stripped from the order.
     */
    const WRAPPED_ORDERITEM_TYPES = [
        OrderItemType::VARIATION,
        OrderItemType::ITEM_BUNDLE,
        OrderItemType::BUNDLE_COMPONENT,
        OrderItemType::PROMOTIONAL_COUPON,
        OrderItemType::GIFT_CARD,
        OrderItemType::SHIPPING_COSTS,
        OrderItemType::UNASSIGNED_VARIATION];
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
     * @return LocalizedOrder
     */
    public static function wrap( $order, ...$data ):LocalizedOrder
    {
        if( $order == null )
        {
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

        list( $lang ) = $data;

        $instance = pluginApp( self::class );
        $instance->order = $order;

        $instance->status = [];
        $instance->totals = $orderTotalsService->getAllTotals($order);


        try
        {
            $shippingProfile = $parcelServicePresetRepository->getPresetById( $order->shippingProfileId );
            $instance->shippingProfileId = (int)$order->shippingProfileId;
            foreach( $shippingProfile->parcelServicePresetNames as $name )
            {
                if( $name->lang === $lang )
                {
                    $instance->shippingProfileName = $name->name;
                    break;
                }
            }

            foreach( $shippingProfile->parcelServiceNames as $name )
            {
                if( $name->lang === $lang )
                {
                    $instance->shippingProvider = $name->name;
                    break;
                }
            }

            /** @var OrderTrackingService $orderTrackingService */
            $orderTrackingService = pluginApp(OrderTrackingService::class);
            $instance->trackingURL = $orderTrackingService->getTrackingURL($order, $lang);
        }
        catch(\Exception $e)
        {}

        $frontentPaymentRepository = pluginApp( FrontendPaymentMethodRepositoryContract::class );

        try
        {
            $instance->paymentMethodName = $frontentPaymentRepository->getPaymentMethodNameById( $order->methodOfPaymentId, $lang );
            $instance->paymentMethodIcon = $frontentPaymentRepository->getPaymentMethodIconById( $order->methodOfPaymentId, $lang );
        }
        catch(\Exception $e)
        {}

        $paymentStatusProperty = $order->properties->firstWhere('typeId', OrderPropertyType::PAYMENT_STATUS);
        if($paymentStatusProperty instanceof OrderProperty)
        {
            $instance->paymentStatus = $paymentStatusProperty->value;
        }

        $paymentMethodIdProperty = $order->properties->firstWhere('typeId', OrderPropertyType::PAYMENT_METHOD);
        if($paymentMethodIdProperty instanceof OrderProperty)
        {
            $instance->allowPaymentMethodSwitchFrom = $orderService->allowPaymentMethodSwitchFrom($paymentMethodIdProperty->value, $order->id);
            $instance->paymentMethodListForSwitch = $orderService->getPaymentMethodListForSwitch($paymentMethodIdProperty->value, $order->id);
        }

        $instance->status = $orderStatusService->getOrderStatus($order->id, $order->statusId);

        $orderVariationIds = [];
        foreach( $order->orderItems as $key => $orderItem )
        {
            if(in_array((int)$orderItem->typeId, self::WRAPPED_ORDERITEM_TYPES))
            {

                if( $orderItem->itemVariationId !== 0 )
                {
                    $orderVariationIds[] = $orderItem->itemVariationId;
                }
            }
            else
            {
                unset($order->orderItems[$key]);
            }
        }

        $resultFields = ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_LIST_ITEM );
        foreach( ['attributes.attribute.names.*', 'attributes.value.names.*', 'images.all.urlPreview', 'images.variation.urlPreview'] as $field )
        {
            if (!in_array($field, $resultFields))
            {
                $resultFields[] = $field;
            }
        }

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory->setPage(1, count($orderVariationIds));
        $orderVariations = $itemSearchService->getResult(
            $searchFactory
                ->withLanguage()
                ->withImages()
                ->withDefaultImage()
                ->withUrls()
                ->withBundleComponents()
                ->hasVariationIds( $orderVariationIds )
                ->withResultFields(
                    $resultFields
                )
        );

        foreach( $orderVariations['documents'] as $orderVariation )
        {
            $variationId =  $orderVariation['data']['variation']['id'];
            $instance->variations[$variationId] = $orderVariation['data'];
            $instance->itemURLs[$variationId]   = $urlFilter->buildItemURL( $orderVariation['data'] );
            $instance->itemImages[$variationId] = $imageFilter->getFirstItemImageUrl( $orderVariation['data']['images'], 'urlPreview' );

            foreach( $instance->order->relations['orderItems'] as $orderItem)
            {
                if($orderItem['itemVariationId'] == $orderVariation['data']['variation']['id'])
                {
                    $orderItem['bundleComponents'] = $orderVariation['data']['bundleComponents'];
                    $orderItem['bundleType'] = $orderVariation['data']['variation']['bundleType'];
                    $attributes = [];

                    foreach($orderVariation['data']['attributes'] as $attribute)
                    {
                        $attributes[] = [
                            'name' => $attribute['attribute']['names']['name'],
                            'value' => $attribute['value']['names']['name']
                        ];
                    }

                    $orderItem['attributes'] = $attributes;
                }
            }
        }

        /** @var OrderTotalsService $orderTotalsService */
        $orderTotalsService = pluginApp(OrderTotalsService::class);
        $instance->highlightNetPrices = $orderTotalsService->highlightNetPrices($instance->order);

        return $instance;
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        $order = $this->order->toArray();
        $order['billingAddress'] = $this->order->billingAddress->toArray();
        $order['deliveryAddress'] = $this->order->deliveryAddress->toArray();
        $order['documents'] = $this->order->documents->toArray();

        if ( count( $this->orderData ) )
        {
            $order = $this->orderData;
        }
        $data = [
            "order"                        => $order,
            "status"                       => $this->status,
            "totals"                       => $this->totals,
            "shippingProfileId"            => $this->shippingProfileId,
            "shippingProvider"             => $this->shippingProvider,
            "shippingProfileName"          => $this->shippingProfileName,
            "paymentMethodName"            => $this->paymentMethodName,
            "paymentMethodIcon"            => $this->paymentMethodIcon,
            "paymentStatus"                => $this->paymentStatus,
            "allowPaymentMethodSwitchFrom" => $this->allowPaymentMethodSwitchFrom,
            "paymentMethodListForSwitch"   => $this->paymentMethodListForSwitch,
            "itemURLs"                     => $this->itemURLs,
            "itemImages"                   => $this->itemImages,
            "variations"                   => $this->variations,
            "isReturnable"                 => $this->isReturnable(),
            "highlightNetPrices"           => $this->highlightNetPrices
        ];

        return $data;
    }

    public function isReturnable()
    {
        $order = $this->order->toArray();

        if($order['typeId'] === OrderType::ORDER)
        {
            $orderItems = count($this->orderData)
                ? $this->orderData['orderItems']
                : $order['orderItems'];

            if(!count($orderItems))
            {
                return false;
            }

            $shippingDateSet = false;
            $createdDateUnix = 0;

            $dates = count($this->orderData) ? $this->orderData['dates'] : $order['dates'];
            foreach($dates as $date)
            {
                if($date['typeId'] === 5 && strlen($date['date']))
                {
                    $shippingDateSet = true;
                }
                elseif($date['typeId'] === 2 && strlen($date['date']))
                {
                    $createdDateUnix = strtotime($date['date']);
                }
            }

            /**  @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $returnTime = (int)$templateConfigService->get('my_account.order_return_days', 14);

            return $shippingDateSet
                && $createdDateUnix > 0
                && $returnTime > 0
                && time() < $createdDateUnix + ($returnTime * 24 * 60 * 60);

        }

        return false;
    }
}
