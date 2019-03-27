<?php

namespace IO\Extensions\Basket;

use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Services\NotificationService;
use Plenty\Modules\Frontend\Events\FrontendShippingProfileChanged;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelServicePreset;

class IOFrontendShippingProfileChanged
{
    public function handle(FrontendShippingProfileChanged $event)
    {
        $selectedShippingProfileId = $event->getShippingProfileId();

        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);
        $deliveryAddressId = $basketService->getDeliveryAddressId();

        if (is_null($deliveryAddressId) || $deliveryAddressId === -99)
        {
            return;
        }

        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);
        $selectedDeliveryAddress = $customerService->getAddress($deliveryAddressId, 2);

        /** @var ParcelServicePresetRepositoryContract $parcelServiceRepository */
        $parcelServiceRepository = pluginApp(ParcelServicePresetRepositoryContract::class);
        $selectedShippingProfile = $parcelServiceRepository->getPresetById($selectedShippingProfileId);

        if ($selectedShippingProfile instanceof ParcelServicePreset)
        {
            $isPostOfficeAndParcelBoxActive = $selectedShippingProfile->isParcelBox && $selectedShippingProfile->isPostOffice;
            $isAddressPostOffice = $selectedDeliveryAddress->address1 === "POSTFILIALE";
            $isAddressParcelBox = $selectedDeliveryAddress->address1 === "PACKSTATION";

            if (!$isPostOfficeAndParcelBoxActive && ($isAddressPostOffice || $isAddressParcelBox))
            {
                $isUnsupportedPostOffice = $isAddressPostOffice && !$selectedShippingProfile->isPostOffice;
                $isUnsupportedParcelBox = $isAddressParcelBox && !$selectedShippingProfile->isParcelBox;

                if ($isUnsupportedPostOffice || $isUnsupportedParcelBox)
                {
                    $basketService->setDeliveryAddressId(-99);
                }
            }
        }
    }
}
