<?php

namespace IO\Models;

use IO\Builder\Order\OrderItemType;
use IO\Builder\Order\OrderType;
use IO\Extensions\Filters\ItemImagesFilter;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\OrderService;
use IO\Services\OrderTotalsService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Property\Models\OrderProperty;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use IO\Extensions\Filters\URLFilter;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelService;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;

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

        list( $lang ) = $data;

        $instance = pluginApp( self::class );
        $instance->order = $order;

        $instance->status = [];
        $instance->totals = pluginApp(OrderTotalsService::class)->getAllTotals($order);

        /**
         * @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository
         */
        $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);

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
    
            $parcelService = $shippingProfile->parcelService;
            if($parcelService instanceof ParcelService)
            {
                $trackingURL = $parcelService->trackingUrl;
                $packageNumber = $order->packagenum;
                $zip = $order->deliveryAddress->postalCode;
        
                if(strlen($trackingURL) && strlen($packageNumber))
                {
                    $trackingURL = str_replace('[PaketNr]',
                                               $packageNumber,
                                               str_replace('[PLZ]',
                                                           $zip,
                                                           str_replace('[Lang]',
                                                                       $lang,
                                                                       $trackingURL)));
            
                    $trackingURL = str_replace('$PaketNr',
                                               $packageNumber,
                                               str_replace('$PLZ',
                                                           $zip,
                                                           str_replace('$Lang',
                                                                       $lang,
                                                                       $trackingURL)));
            
                    $instance->trackingURL = $trackingURL;
                }
            }
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
            /** @var OrderService $orderService */
            $orderService = pluginApp(OrderService::class);

            $instance->allowPaymentMethodSwitchFrom = $orderService->allowPaymentMethodSwitchFrom($paymentMethodIdProperty->value, $order->id);
            $instance->paymentMethodListForSwitch = $orderService->getPaymentMethodListForSwitch($paymentMethodIdProperty->value, $order->id);
        }

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $orderStatus = $authHelper->processUnguarded( function() use ($order)
        {
            /** @var OrderStatusRepositoryContract $orderStatusRepository */
            $orderStatusRepository = pluginApp(OrderStatusRepositoryContract::class);
            return $orderStatusRepository->get($order->statusId);
        });

        if ( !is_null($orderStatus) )
        {
            $instance->status = $orderStatus->toArray();
        }

        /** @var URLFilter $urlFilter */
        $urlFilter = pluginApp(URLFilter::class);

        /** @var ItemImagesFilter $imageFilter */
        $imageFilter = pluginApp( ItemImagesFilter::class );

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
        );

        foreach( $orderVariations['documents'] as $orderVariation )
        {
            $variationId =  $orderVariation['data']['variation']['id'];
            $instance->itemURLs[$variationId]   = $urlFilter->buildItemURL( $orderVariation['data'] );
            $instance->itemImages[$variationId] = $imageFilter->getFirstItemImageUrl( $orderVariation['data']['images'], 'urlPreview' );

            foreach( $instance->order->relations['orderItems'] as $orderItem)
            {
                if($orderItem['itemVariationId'] == $orderVariation['data']['variation']['id'])
                {
                    $orderItem['bundleComponents'] = $orderVariation['data']['bundleComponents'];
                    $orderItem['bundleType'] = $orderVariation['data']['variation']['bundleType'];
                }
            }
        }

        if ($order->typeId == OrderType::ORDER)
        {
            $instance->isReturnable = $orderService->isOrderReturnable($order);
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
            "totals"                => $this->totals,
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
            "isReturnable"                 => $this->isReturnable,
            "highlightNetPrices"           => $this->highlightNetPrices
        ];

        return $data;
    }
}
