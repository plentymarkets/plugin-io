<?php

namespace IO\Validators\Customer;

use Plenty\Validation\Validator;
use IO\Services\TemplateConfigService;

class DeliveryAddressValidator extends Validator
{
    private $requiredFields;
    private $shownFields;
    
    public function defineAttributes()
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $requiredFieldsString  = $templateConfigService->get('delivery_address.require');
        $this->requiredFields  = explode(', ', $requiredFieldsString);
        $shownFieldsString  = $templateConfigService->get('delivery_address.show');
        $this->shownFields  = explode(', ', $shownFieldsString);
        foreach ($this->requiredFields as $key => $value)
        {
            $this->requiredFields[$key] = str_replace('delivery_address.', '', $value);
        }
        
        $this->addString('name2',      true);
        $this->addString('name3',      true);
        $this->addString('address1',   true);
        $this->addString('address2',   true);
        $this->addString('postalCode', true);
        $this->addString('town',       true);
        
        if(count($this->requiredFields))
        {
            $this->addString('name1',     $this->isRequired('name1'));
            $this->addString('name4',     $this->isRequired('name4'));
            $this->addString('address3',  $this->isRequired('address3'));
            $this->addString('address4',  $this->isRequired('address4'));
            $this->addString('stateId',  $this->isRequired('stateId'));
            $this->addString('title',     $this->isRequired('title'));
            $this->addString('telephone', $this->isRequired('telephone'));
        }
    }
    
    private function isRequired($fieldName)
    {
        return in_array($fieldName, $this->shownFields) && in_array($fieldName, $this->requiredFields);
    }
}