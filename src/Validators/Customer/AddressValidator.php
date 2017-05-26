<?php

namespace IO\Validators\Customer;

use IO\Builder\Order\AddressType;
use IO\Services\CheckoutService;
use IO\Constants\ShippingCountry;
use IO\Validators\Customer\BillingAddressValidator;
use IO\Validators\Customer\DeliveryAddressValidator;
use IO\Validators\Customer\BillingAddressValidatorEN;
use IO\Validators\Customer\DeliveryAddressValidatorEN;

class AddressValidator
{
    public static function validateOrFail($addressType, $addressData)
    {
        if($addressType == AddressType::BILLING)
        {
            if(self::isEnAddress())
            {
                BillingAddressValidatorEN::validateOrFail($addressData);
            }
            else
            {
                BillingAddressValidator::validateOrFail($addressData);
            }
            
        }
        elseif($addressType == AddressType::DELIVERY)
        {
            if(self::isEnAddress())
            {
                DeliveryAddressValidatorEN::validateOrFail($addressData);
            }
            else
            {
                DeliveryAddressValidator::validateOrFail($addressData);
            }
        }
        else
        {
            if(self::isEnAddress())
            {
                BillingAddressValidatorEN::validateOrFail($addressData);
            }
            else
            {
                BillingAddressValidator::validateOrFail($addressData);
            }
        }
    }
    
    private static function isEnAddress()
    {
        /**
         * @var CheckoutService $checkoutService
         */
        $checkoutService = pluginApp(CheckoutService::class);
        $shippingCountryId = $checkoutService->getShippingCountryId();
        
        if($shippingCountryId == ShippingCountry::UNITED_KINGDOM || $shippingCountryId == ShippingCountry::IRELAND)
        {
            return true;
        }
        
        return false;
    }
}