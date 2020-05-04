<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class PropertyFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $propertyId   = $this->number();
        $groupId      = $this->number();
        $propertyType = $this->rand(['empty', 'int', 'float', 'selection', 'text', 'file']);
        $default      = [
            "surcharge"     => $this->float(),
            "valueFloat"    => $propertyType === 'float' ? $this->float() : 0,
            "valueInt"      => $propertyType === 'int' ? $this->number() : 0,
            "property"      => $this->makeProperty($propertyId, $propertyType, $groupId),
            "group"         => $this->makeGroup($groupId),
            "selection"     => $propertyType === 'selection' ? $this->makeSelection($propertyId) : null,
            "texts"         => [
                "valueId"   => $this->number(),
                "lang"      => $this->lang,
                "value"     => $this->text(0, 20)
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeProperty($propertyId, $propertyType, $groupId)
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
            "isOderProperty"            => false,
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

    private function makeGroup($groupId)
    {
        $groupName = $this->trans("IO::Faker.propertyGroupName");
        return [
            "id"                        => $groupId,
            "backendName"               => $groupName,
            "isSurchargePercental"      => $this->boolean(),
            "orderPropertyGroupingType" => $this->rand(['none', 'single', 'multi']),
            "ottoComponent"             => $this->number(),
            "updatedAt"                 => $this->dateString(),
            "names"                     => [
                [
                    "propertyGroupId"   => $groupId,
                    "lang"              => $this->lang,
                    "name"              => $groupName,
                    "description"       => $this->text(5, 15)
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