<?php

namespace IO\Services\Order\Factories\Faker;

use IO\Extensions\Filters\ItemImagesFilter;
use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;
use Plenty\Modules\Order\Status\Models\OrderStatus;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Translation\Translator;

class LocalizedOrderFaker extends AbstractFaker
{
    public function fill($data, $variations = [])
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);
        
        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $lang = $sessionStorageService->getLang();
    
        $paymentMethodName = '';
        $paymentMethodIcon = '';
        
        /** @var FrontendPaymentMethodRepositoryContract $frontentPaymentRepository */
        $frontendPaymentRepository = pluginApp( FrontendPaymentMethodRepositoryContract::class );
        $paymentMethodList = $frontendPaymentRepository->getCurrentPaymentMethodsList();
        
        if(count($paymentMethodList))
        {
            $paymentMethod = $this->rand($paymentMethodList);
            if($paymentMethod instanceof PaymentMethod)
            {
                $paymentMethodName = $frontendPaymentRepository->getPaymentMethodName($paymentMethod, $lang);
                $paymentMethodIcon = $frontendPaymentRepository->getPaymentMethodIcon($paymentMethod, $lang);
            }
        }
        else
        {
            $paymentMethodName = $translator->trans('IO::Faker.paymentMethodName');
        }
        
        $shippingProfileName = '';
        $shippingProvider = '';
        
        /**
         * @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository
         */
        $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);
    
        $shippingProfileList = $parcelServicePresetRepository->getPresetList();
        $shippingProfile = $this->rand($shippingProfileList);
        foreach( $shippingProfile->parcelServicePresetNames as $name )
        {
            if( $name->lang === $lang )
            {
                $shippingProfileName = $name->name;
                break;
            }
        }
    
        foreach( $shippingProfile->parcelServiceNames as $name )
        {
            if( $name->lang === $lang )
            {
                $shippingProvider = $name->name;
                break;
            }
        }
    
        $orderStatusName = '';
        
        /** @var OrderStatusRepositoryContract $orderStatusRepo */
        $orderStatusRepo = pluginApp(OrderStatusRepositoryContract::class);
    
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
    
        $orderStatusList = $authHelper->processUnguarded(function() use ($orderStatusRepo){
            return $orderStatusRepo->all();
        });
    
        if(count($orderStatusList))
        {
            $orderStatus = $this->rand($orderStatusList);
            if($orderStatus instanceof OrderStatus)
            {
                $orderStatusName = $orderStatus->names[$lang];
            }
        }
        else
        {
            $orderStatusName = $translator->trans('IO::Faker.orderStatusName');
        }
    
        /** @var ItemImagesFilter $imageFilter */
        $imageFilter = pluginApp(ItemImagesFilter::class);

        $itemImages = [];
        foreach($data['order']['orderItems'] as $orderItem)
        {
            foreach($orderItem['images'] as $variationId => $imageUrl)
            {
                $itemImages[$variationId] = $imageFilter->getFirstItemImageUrl( $variations[$variationId]['images'], 'urlMiddle' );
            }
        }
        
        $default = [
            'paymentMethodName'   => $paymentMethodName,
            'paymentMethodIcon'   => $paymentMethodIcon,
            'shippingProfileName' => $shippingProfileName,
            'shippingProvider'    => $shippingProvider,
            'status'              => $orderStatusName,
            'itemImages'          => $itemImages,
            'variations'          => $variations
        ];
        
        $this->merge($data, $default);
        return $data;
    }
}
