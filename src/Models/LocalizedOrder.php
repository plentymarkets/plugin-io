<?php

namespace IO\Models;

use IO\Builder\Order\OrderType;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Status\Models\OrderStatusName;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
//use Plenty\Modules\Order\Status\Contracts\StatusRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use IO\Extensions\Filters\URLFilter;
use IO\Services\ItemService;
use IO\Services\OrderService;

class LocalizedOrder extends ModelWrapper
{
    /**
     * @var Order
     */
    public $order = null;

    /**
     * @var OrderStatusName
     */
    public $status = null;

    public $shippingProvider = "";
    public $shippingProfileName = "";
    public $paymentMethodName = "";
    public $paymentMethodIcon = "";

    public $itemURLs = [];
    public $itemImages = [];
    public $isReturnable = false;

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

        //$statusRepository = pluginApp(StatusRepositoryContract::class);
        $instance->status = []; //$statusRepository->findStatusNameById( $order->statusId, $lang );

        $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);
        if($parcelServicePresetRepository instanceof ParcelServicePresetRepositoryContract)
        {
            
        }
        $shippingProfile = $parcelServicePresetRepository->getPresetById( $order->shippingProfileId );
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

        $frontentPaymentRepository = pluginApp( FrontendPaymentMethodRepositoryContract::class );
        $instance->paymentMethodName = $frontentPaymentRepository->getPaymentMethodNameById( $order->methodOfPaymentId, $lang );
        $instance->paymentMethodIcon = $frontentPaymentRepository->getPaymentMethodIconById( $order->methodOfPaymentId, $lang );


        $urlFilter = pluginApp(URLFilter::class);
        $itemService = pluginApp(ItemService::class);

        foreach( $order->orderItems as $key => $orderItem )
        {
            if($orderItem->typeId == 1 || $orderItem->typeId == 3 || $orderItem->typeId == 9)
            {
                
                if( $orderItem->itemVariationId !== 0 )
                {
                    $itemUrl = '';
                    if((INT)$orderItem->itemVariationId > 0)
                    {
                        $itemUrl = $urlFilter->buildVariationURL($orderItem->itemVariationId, true);
                    }
    
                    $instance->itemURLs[$orderItem->itemVariationId] = $itemUrl;
    
                    $itemImage = $itemService->getVariationImage($orderItem->itemVariationId);
                    $instance->itemImages[$orderItem->itemVariationId] = $itemImage;
                }
            }
            else
            {
                unset($order->orderItems[$key]);
            }
        }
        
        if($order->typeId == OrderType::ORDER)
        {
            $orderService = pluginApp(OrderService::class);
            $instance->isReturnable = $orderService->isOrderReturnable($order);
        }

        return $instance;
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        $data = [
            "order"                 => $this->order->toArray(),
            "status"                => [], //$this->status->toArray(),
            "shippingProvider"      => $this->shippingProvider,
            "shippingProfileName"   => $this->shippingProfileName,
            "paymentMethodName"     => $this->paymentMethodName,
            "paymentMethodIcon"     => $this->paymentMethodIcon,
            "itemURLs"              => $this->itemURLs,
            "itemImages"            => $this->itemImages,
            "isReturnable"          => $this->isReturnable
        ];

        $data["order"]["billingAddress"] = $this->order->billingAddress->toArray();
        $data["order"]["deliveryAddress"] = $this->order->deliveryAddress->toArray();

        return $data;
    }
}
