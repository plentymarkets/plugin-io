<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class OrderPropertyFaker extends AbstractFaker
{
    public $isList = true;
    
    public function fill($data)
    {
        $propertyId   = $this->number();
        $groupId      = null;
        $propertyType = $this->rand(['int', 'float', 'selection', 'text']);
        $isOrderProperty = true;
        $default      = [
            "surcharge"     => $this->float(),
            "property"      => $this->makeProperty($propertyId, $propertyType, $groupId, $isOrderProperty),
            "group"         => null,
        ];
        
        if($isOrderProperty)
        {
            $default["valueFloat"]   = $propertyType === 'float' ? $this->float() : 0;
            $default["valueInt"]     = $propertyType === 'int' ? $this->number() : 0;
            $default["selection"]    = $propertyType === 'selection' ? $this->makeSelection($propertyId) : null;
            $default["texts"]        = [
                "valueId"   => $this->number(),
                "lang"      => $this->lang,
                "value"     => $this->text(0, 20)
            ];
        }
        
        $this->merge($data, $default);
        return $data;
    }
    
    private function makeProperty($propertyId, $propertyType, $groupId, $isOrderProperty)
    {
        $propertyName = $this->trans("IO::Faker.propertyName");
        return [
            "id"                        => $propertyId,
            "position"                  => $this->number(0, 100),
            "unit"                      => $this->word(),
            "propertyGroupId"           => $groupId,
            "backendName"               => $propertyName,
            "valueType"                 => $propertyType,
            "isSearchable"              => $this->boolean(),
            "isOderProperty"            => $isOrderProperty,
            "isShownOnItemPage"         => true,
            "isShownOnItemList"         => true,
            "isShownAtCheckout"         => $this->boolean(),
            "isShownInPdf"              => $this->boolean(),
            "comment"                   => $this->text(5, 15),
            "surcharge"                 => $this->float(),
            "isShownAsAdditionalCosts"  => $this->boolean(),
            "updatedAt"                 => $this->dateString(),
            "names"                     => [
                [
                    "propertyId"    => $propertyId,
                    "lang"          => $this->lang,
                    "name"          => $propertyName,
                    "description"   => $this->text()
                ]
            ]
        ];
    }
    
    private function makeSelection($propertyId)
    {
        return [
            "id"            => $this->number(),
            "propertyId"    => $propertyId,
            "lang"          => $this->lang,
            "name"          => $this->word(),
            "description"   => $this->text(5, 15)
        ];
    }
}