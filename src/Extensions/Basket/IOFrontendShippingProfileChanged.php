<?php

namespace IO\Extensions\Basket;

use IO\Services\BasketService;
use IO\Services\CustomerService;
use Plenty\Modules\Frontend\Events\FrontendShippingProfileChanged;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;

class IOFrontendShippingProfileChanged
{
    public function handle(FrontendShippingProfileChanged $event)
    {
        $selectedShippingProfileId = $event->getShippingProfileId();

        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);
        $deliveryAddressId = $basketService->getDeliveryAddressId();

        if (is_null($deliveryAddressId))
        {
            return;
        }

        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);
        $selectedDeliveryAddress = $customerService->getAddress($deliveryAddressId, 2);

        /** @var ParcelServicePresetRepositoryContract $parcelServiceRepository */
        $parcelServiceRepository = pluginApp(ParcelServicePresetRepositoryContract::class);
        $selectedShippingProfile = $parcelServiceRepository->getPresetById($selectedShippingProfileId);

        if (!is_null($selectedShippingProfile))
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

                    // TODO: dem Nutzer bescheid sagen, dass das Profil geändert wurde.
                }
            }
        }
    }
}
