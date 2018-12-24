<?php

namespace IO\Validators\Customer;

use Plenty\Validation\Validator;
use IO\Services\TemplateConfigService;

class BillingAddressValidatorEN extends Validator
{
    private $requiredFields;
    private $shownFields;

    public static $addressData;
    
    public function defineAttributes()
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $requiredFieldsString  = $templateConfigService->get('billing_address.en.require');
        $this->requiredFields  = explode(', ', $requiredFieldsString);
        $shownFieldsString     = $templateConfigService->get('billing_address.en.show');
        $this->shownFields     = explode(', ', $shownFieldsString);
        foreach ($this->requiredFields as $key => $value)
        {
            $this->requiredFields[$key] = str_replace('billing_address.', '', $value);
        }

        foreach ($this->shownFields as $key => $value)
        {
            $this->shownFields[$key] = str_replace('billing_address.', '', $value);
        }
    
        $this->addString('address1', true);
        $this->addString('postalCode', true);
        $this->addString('town',       true);

        if($this->isShown('salutation'))
        {
            $hasContactPerson = $this->isShown('name1') && empty(self::$addressData['gender']);

            $this->addString('name1', $hasContactPerson);
            $this->addString('name2', !$hasContactPerson);
            $this->addString('name3', !$hasContactPerson);
            $this->addString('contactPerson', $hasContactPerson);
        }
        else
        {
            $hasName1 = $this->isShown('name1');

            $this->addString('name1', $hasName1);
            $this->addString('name2', !$hasName1);
            $this->addString('name3', !$hasName1);
            $this->addString('contactPerson', $hasName1);
        }

        if(count($this->requiredFields))
        {
            if(empty(self::$addressData['gender']))
            {
                $this->addString('vatNumber', $this->isRequired('vatNumber'));
            }

            $this->addString('birthday',  $this->isRequired('birthday'));
            $this->addString('name4',     $this->isRequired('name4'));
            $this->addString('address2',  $this->isRequired('address2'));
            $this->addString('address3',  $this->isRequired('address3'));
            $this->addString('address4',  $this->isRequired('address4'));
            $this->addString('title',     $this->isRequired('title'));
            $this->addString('telephone', $this->isRequired('telephone'));
        }
    }
    
    private function isRequired($fieldName)
    {
        return in_array($fieldName, $this->shownFields) && in_array($fieldName, $this->requiredFields);
    }

    private function isShown($fieldName)
    {
        return in_array($fieldName, $this->shownFields);
    }
}
