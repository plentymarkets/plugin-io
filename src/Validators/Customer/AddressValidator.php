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
        
        
        $name1Condition = $this->addString('name1', $isCompany);
        $name2Condition = $this->addString('name2', !$isCompany);
        $name3Condition = $this->addString('name3', !$isCompany);
        
        if($isCompany)
        {
            $name2Condition->nullable();
            $name3Condition->nullable();
        }
        else
        {
            $name1Condition->nullable();
        }
        
        $this->addDate('birthday')->date()->dateBefore(date('Y-m-d'));
    }
}