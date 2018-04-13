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
        if($addressType == AddressType::DELIVERY)
        {
            if(self::isEnAddress($addressData['countryId']))
            {
                DeliveryAddressValidatorEN::$addressData = $addressData;
                DeliveryAddressValidatorEN::validateOrFail($addressData);
            }
            else
            {
                DeliveryAddressValidator::$addressData = $addressData;
                DeliveryAddressValidator::validateOrFail($addressData);
            }
        }
        else
        {
            if(self::isEnAddress($addressData['countryId']))
            {
                BillingAddressValidatorEN::$addressData = $addressData;
                BillingAddressValidatorEN::validateOrFail($addressData);
            }
            else
            {
                BillingAddressValidator::$addressData = $addressData;
                BillingAddressValidator::validateOrFail($addressData);
            }
        }
    }
    
    public static function isEnAddress($shippingCountryId)
    {
        if($shippingCountryId == ShippingCountry::UNITED_KINGDOM || $shippingCountryId == ShippingCountry::IRELAND)
        {
            return true;
        }
        
        return false;
    }
}