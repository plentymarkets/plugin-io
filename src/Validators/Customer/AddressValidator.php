<?php

namespace IO\Validators\Customer;

use IO\Constants\ShippingCountry;
use Plenty\Validation\Validator;

class AddressValidator extends Validator
{
    protected function defineAttributes()
    {
        $isCompany = empty($this->getAttributeValue('gender'));
        $addressFormat = ShippingCountry::getAddressFormat($this->getAttributeValue('countryId'));

        $this->addString('address1', true);
        $this->addString('address2', $addressFormat === ShippingCountry::ADDRESS_FORMAT_EN);
        $this->addString('postalCode', true);
        $this->addString('town', true);
        $this->addString('name1', $isCompany);
        $this->addString('name2', !$isCompany);
        $this->addString('name3', !$isCompany);
        $this->addString('contactPerson', $isCompany);
    }
}