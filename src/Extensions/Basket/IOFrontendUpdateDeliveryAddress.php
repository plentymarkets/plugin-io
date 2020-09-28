<?php

namespace IO\Extensions\Basket;

use Illuminate\Support\Collection;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;

// TODO When the class is moved into the core package, make sure this hook is processed after the current internal hooks

/**
 * Class IOFrontendUpdateDeliveryAddress
 *
 * Set the delivery address to basket and session object
 */
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
        $selectedShippingProfile = $this->firstWhere($shippingProfileList, "parcelServicePresetId", $checkoutService->getShippingProfileId());

        $isAddressPostOffice = $selectedDeliveryAddress->address1 === "POSTFILIALE";
        $isAddressParcelBox = $selectedDeliveryAddress->address1 === "PACKSTATION";

        $isUnsupportedPostOffice = $isAddressPostOffice && !$selectedShippingProfile->isPostOffice;
        $isUnsupportedParcelBox = $isAddressParcelBox && !$selectedShippingProfile->isParcelBox;

        if ($isUnsupportedPostOffice || $isUnsupportedParcelBox)
        {
            $profileToSelect = null;

            if ($isUnsupportedPostOffice)
            {
                $profileToSelect = $this->firstWhere($shippingProfileList, "isPostOffice", true);
            }
            else
            {
                $profileToSelect = $this->firstWhere($shippingProfileList, "isParcelBox", true);
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

    private function firstWhere($collection, $key, $expected)
    {
        foreach($collection as $value)
        {
            if ( $value[$key] === $expected )
            {
                return $value;
            }
        }

        return null;
    }
}
