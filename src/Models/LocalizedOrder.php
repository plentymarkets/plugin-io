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


        return $instance;
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        return [
            "order"                 => $this->order->toArray(),
            "status"                => $this->status->toArray(),
            "shippingProvider"      => $this->shippingProvider,
            "shippingProfileName"   => $this->shippingProfileName,
            "paymentMethodName"     => $this->paymentMethodName,
            "paymentMethodIcon"     => $this->paymentMethodIcon
        ];
    }
}