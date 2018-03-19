<?php

namespace IO\Models;

use IO\Builder\Order\OrderType;
use IO\Extensions\Filters\ItemImagesFilter;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Services\ItemSearchService;
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
     * The OrderItem types that will be wrapped. All other OrderItems will be stripped from the order.
     */
    const WRAPPED_ORDERITEM_TYPES = [1, 3, 4, 6, 9];
    /**
     * @var Order
     */
    public $order = null;
    
    public $orderData = [];

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

        $instance->status = [];
    
        /**
         * @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository
         */
        $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);
        
        try
        {
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


        /** @var URLFilter $urlFilter */
        $urlFilter = pluginApp(URLFilter::class);

        /** @var ItemImagesFilter $imageFilter */
        $imageFilter = pluginApp( ItemImagesFilter::class );

        $orderVariationIds = [];
        foreach( $order->orderItems as $key => $orderItem )
        {
            if(in_array($orderItem->typeId, self::WRAPPED_ORDERITEM_TYPES))
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
        $orderVariations = $itemSearchService->getResults(
            $searchFactory
                ->withLanguage()
                ->withImages()
                ->withUrls()
                ->hasVariationIds( $orderVariationIds )
        );

        foreach( $orderVariations['documents'] as $orderVariation )
        {
            $variationId = $orderVariation['data']['variation']['id'];
            $instance->itemURLs[$variationId]   = $urlFilter->buildItemURL( $orderVariation['data'] );
            $instance->itemImages[$variationId] = $imageFilter->getFirstItemImageUrl( $orderVariation['data']['images'], 'urlPreview' );
        }

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
        if ( count( $this->orderData ) )
        {
            $order = $this->orderData;
        }
        $data = [
            "order"                 => $order,
            "status"                => [], //$this->status->toArray(),
            "shippingProvider"      => $this->shippingProvider,
            "shippingProfileName"   => $this->shippingProfileName,
            "paymentMethodName"     => $this->paymentMethodName,
            "paymentMethodIcon"     => $this->paymentMethodIcon,
            "itemURLs"              => $this->itemURLs,
            "itemImages"            => $this->itemImages,
            "isReturnable"          => $this->isReturnable
        ];

        return $data;
    }
}
