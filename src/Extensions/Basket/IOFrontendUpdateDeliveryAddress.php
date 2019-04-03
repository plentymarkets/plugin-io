<?php

namespace IO\Extensions\Basket;

use Illuminate\Support\Collection;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;

class IOFrontendUpdateDeliveryAddress
{
    public function handle(FrontendUpdateDeliveryAddress $event)
    {
        $deliveryAddressId = $event->getAccountAddressId();

        if (is_null($deliveryAddressId) || $deliveryAddressId === -99)
        {
            return;
        }

        /** @var CheckoutService $checkoutService */
        $checkoutService = pluginApp(CheckoutService::class);

        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);

        $selectedDeliveryAddress = $customerService->getAddress($deliveryAddressId, 2);

        $shippingProfileList = $checkoutService->getShippingProfileList();
        $shippingProfileList = pluginApp(Collection::class, [ $shippingProfileList ]);
        $selectedShippingProfile = $shippingProfileList->firstWhere("parcelServicePresetId", $checkoutService->getShippingProfileId());

        $isPostOfficeAndParcelBoxActive = $selectedShippingProfile["isPostOffice"] && $selectedShippingProfile["isParcelBox"];
        $isAddressPostOffice = $selectedDeliveryAddress->address1 === "POSTFILIALE";
        $isAddressParcelBox = $selectedDeliveryAddress->address1 === "PACKSTATION";

        if (!$isPostOfficeAndParcelBoxActive && ($isAddressPostOffice || $isAddressParcelBox))
        {
            $isUnsupportedPostOffice = $isAddressPostOffice && !$selectedShippingProfile->isPostOffice;
            $isUnsupportedParcelBox = $isAddressParcelBox && !$selectedShippingProfile->isParcelBox;

            if ($isUnsupportedPostOffice || $isUnsupportedParcelBox)
            {
                $profileToSelect = null;

                if ($isUnsupportedPostOffice)
                {
                    $profileToSelect = $shippingProfileList->firstWhere("isPostOffice", true);
                }
                else
                {
                    $profileToSelect = $shippingProfileList->firstWhere("isParcelBox", true);
                }

                if (!is_null($profileToSelect))
                {
                    $checkoutService->setShippingProfileId($profileToSelect["parcelServicePresetId"]);
                }
                else
                {
                    $checkoutService->setDeliveryAddressId(-99);
                }
            }
        }
    }
}