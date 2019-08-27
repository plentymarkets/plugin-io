<?php

namespace IO\Services\Order\Factories\Faker;

use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;

class LocalizedOrderFaker extends AbstractFaker
{
    public function fill($data)
    {
        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $lang = $sessionStorageService->getLang();
    
        /** @var FrontendPaymentMethodRepositoryContract $frontentPaymentRepository */
        $frontendPaymentRepository = pluginApp( FrontendPaymentMethodRepositoryContract::class );
        $paymentMethodList = $frontendPaymentRepository->getCurrentPaymentMethodsList();
    
        $paymentMethod = $this->rand($paymentMethodList);
    
    
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
    
        /** @var OrderStatusRepositoryContract $orderStatusRepo */
        $orderStatusRepo = pluginApp(OrderStatusRepositoryContract::class);
    
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
    
        $orderStatusList = $authHelper->processUnguarded(function() use ($orderStatusRepo){
            return $orderStatusRepo->all();
        });
    
        $orderStatus = $this->rand($orderStatusList);
        $orderStatusName = $orderStatus->names[$lang];
    
        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);
        $webstoreConfig = $webstoreConfigService->getWebstoreConfig();
    
        $itemImages = [];
        foreach($data['order']['orderItems'] as $orderItem)
        {
            foreach($orderItem['images'] as $variationId => $imageUrl)
            {
                $itemImages[$variationId] = $webstoreConfig->domainSsl.'/'.$imageUrl;
            }
        }
        
        $default = [
            'paymentMethodName'   => $frontendPaymentRepository->getPaymentMethodName($paymentMethod, $lang),
            'paymentMethodIcon'   => $frontendPaymentRepository->getPaymentMethodIcon($paymentMethod, $lang),
            'shippingProfileName' => $shippingProfileName,
            'shippingProvider'    => $shippingProvider,
            'status'              => $orderStatusName,
            'itemImages'          => $itemImages
        ];
        
        $this->merge($data, $default);
        return $data;
    }
}
