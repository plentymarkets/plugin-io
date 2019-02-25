<?php

namespace IO\Models;

use IO\Builder\Order\OrderType;
use IO\Builder\Order\OrderItemType;
use IO\Extensions\Filters\ItemImagesFilter;
use IO\Services\CustomerService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Status\Models\OrderStatusName;
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

    /**
     * @var OrderStatusName
     */
    public $status = null;

    public $shippingProvider = "";
    public $shippingProfileName = "";
    public $shippingProfileId = 0;
    public $paymentMethodName = "";
    public $paymentMethodIcon = "";

    public $itemURLs = [];
    public $itemImages = [];
    public $isReturnable = false;

    public $highlightNetPrices = false;

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
        $orderVariations = $itemSearchService->getResults(
            $searchFactory
                ->withLanguage()
                ->withImages()
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

        $instance->highlightNetPrices = $instance->highlightNetPrices();

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
            "shippingProfileId"     => $this->shippingProfileId,
            "shippingProvider"      => $this->shippingProvider,
            "shippingProfileName"   => $this->shippingProfileName,
            "paymentMethodName"     => $this->paymentMethodName,
            "paymentMethodIcon"     => $this->paymentMethodIcon,
            "itemURLs"              => $this->itemURLs,
            "itemImages"            => $this->itemImages,
            "isReturnable"          => $this->isReturnable,
            "highlightNetPrices"    => $this->highlightNetPrices
        ];

        return $data;
    }

    private function highlightNetPrices()
    {
        $isOrderNet = $this->order->amounts[0]->isNet;

        $orderContactId = 0;
        foreach ($this->order->relations['relations'] as $relation)
        {
            if ($relation['referenceType'] == 'contact' && (int)$relation['referenceId'] > 0)
            {
                $orderContactId = $relation['referenceId'];
            }
        }

        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);

        $showNet = $customerService->showNetPricesByContactId($orderContactId);

        return $showNet || $isOrderNet;
    }
}
