<?php

namespace LayoutCore\Models;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelServicePreset;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelServicePresetName;
use Plenty\Modules\Order\Status\Models\OrderStatusName;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;

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

        $statusRepository = pluginApp( \Plenty\Modules\Order\Status\Contracts\StatusRepositoryContract::class );
        $instance->status = $statusRepository->findStatusNameById( $order->statusId, $lang );

        $parcelServicePresetRepository = pluginApp( \Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract::class );
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


        $urlFilter = pluginApp( \LayoutCore\Extensions\Filters\URLFilter::class );
        $itemService = pluginApp( \LayoutCore\Services\ItemService::class );

        foreach( $order->orderItems as $orderItem )
        {
            if( $orderItem->itemVariationId !== 0 )
            {
                $itemUrl = $urlFilter->buildVariationURL($orderItem->itemVariationId, true);
                $instance->itemURLs[$orderItem->itemVariationId] = $itemUrl;

                $itemImage = $itemService->getVariationImage($orderItem->itemVariationId);
                $instance->itemImages[$orderItem->itemVariationId] = $itemImage;
            }

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
            "status"                => $this->status->toArray(),
            "shippingProvider"      => $this->shippingProvider,
            "shippingProfileName"   => $this->shippingProfileName,
            "paymentMethodName"     => $this->paymentMethodName,
            "paymentMethodIcon"     => $this->paymentMethodIcon,
            "itemURLs"              => $this->itemURLs,
            "itemImages"            => $this->itemImages
        ];

        $data["order"]["billingAddress"] = $this->order->billingAddress->toArray();
        $data["order"]["deliveryAddress"] = $this->order->deliveryAddress->toArray();

        return $data;
    }
}