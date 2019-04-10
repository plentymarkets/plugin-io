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

        if ($deliveryAddressId == 0 || is_null($deliveryAddressId) || $deliveryAddressId === -99)
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
        $selectedShippingProfile = $shippingProfileList->where("parcelServicePresetId", $checkoutService->getShippingProfileId())->first();

        $isAddressPostOffice = $selectedDeliveryAddress->address1 === "POSTFILIALE";
        $isAddressParcelBox = $selectedDeliveryAddress->address1 === "PACKSTATION";

        $isUnsupportedPostOffice = $isAddressPostOffice && !$selectedShippingProfile->isPostOffice;
        $isUnsupportedParcelBox = $isAddressParcelBox && !$selectedShippingProfile->isParcelBox;

        if ($isUnsupportedPostOffice || $isUnsupportedParcelBox)
        {
            $profileToSelect = null;

            if ($isUnsupportedPostOffice)
            {
                $profileToSelect = $shippingProfileList->where("isPostOffice", true)->first();
            }
            else
            {
                $profileToSelect = $shippingProfileList->where("isParcelBox", true)->first();
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