<?php

namespace IO\Validators\Customer;

use IO\Constants\ShippingCountry;
use Plenty\Validation\Validator;

class AddressValidator extends Validator
{
    protected function defineAttributes()
    {
        $isCompany = empty($this->getAttributeValue('gender')) && empty($this->getAttributeValue('name2')) && empty($this->getAttributeValue('name3'));
        $addressFormat = ShippingCountry::getAddressFormat($this->getAttributeValue('countryId'));

        $this->addString('address1', true);
        $this->addString('address2', $addressFormat === ShippingCountry::ADDRESS_FORMAT_DE);
        $this->addString('postalCode', true);
        $this->addString('town', true);
        $this->addString('name1', $isCompany)->nullable();
        $this->addString('name2', !$isCompany)->nullable();
        $this->addString('name3', !$isCompany)->nullable();
        $this->addDate('birthday')->date()->dateBefore(date('Y-m-d'));
    }
}