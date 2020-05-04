<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class AttributeFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $attributeId  = $this->number();
        $valueId      = $this->number();
        $default = [
            "attributeValueSetId"   => $this->number(),
            "attributeId"           => $attributeId,
            "valueId"               => $valueId,
            "isLinkableToImage"     => $this->boolean(),
            "attribute"             => $this->makeAttribute($attributeId),
            "value"                 => $this->makeValue($attributeId, $valueId)
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeAttribute($attributeId)
    {
        $attributeName = $this->trans("IO::Faker.attributeName");
        return [
            "id"                            => $attributeId,
            "backendName"                   => $attributeName,
            "position"                      => $this->number(),
            "isSurchargePercental"          => $this->boolean(),
            "isLinkableToImage"             => $this->boolean(),
            "amazonAttribute"               => $attributeName,
            "fruugoAttribute"               => $attributeName,
            "pixmaniaAttribute"             => $this->number(),
            "ottoAttribute"                 => $attributeName,
            "googleShoppingAttribute"       => $attributeName,
            "neckermannAtEpAttribute"       => $this->number(),
            "typeOfSelectionInOnlineStore"  => $this->rand(["dropdown", "image", "box"]),
            "laRedouteAttribute"            => $this->number(),
            "isGroupable"                   => $this->boolean(),
            "updatedAt"                     => $this->dateString("Y-m-d"),
            "names"                         => [
                "attributeId"   => $attributeId,
                "lang"          => $this->lang,
                "name"          => $attributeName
            ]
        ];
    }

    private function makeValue($attributeId, $valueId)
    {
        $valueName = $this->trans("IO::Faker.attributeValueName");
        return [
            "id"                    => $valueId,
            "attributeId"           => $attributeId,
            "backendName"           => $valueName,
            "position"              => $this->number(),
            "image"                 => $this->image(100, 100, $valueName),
            "comment"               => $this->text(1,10),
            "amazonValue"           => $valueName,
            "ottoValue"             => $valueName,
            "neckermannAtEpValue"   => $valueName,
            "laRedouteValue"        => $valueName,
            "tracdelightValue"      => $valueName,
            "percentageDistribution"=> $this->percentage(),
            "updatedAt"             => $this->dateString("Y-m-d"),
            "names"                 => [
                "lang"      => $this->lang,
                "valueId"   => $valueId,
                "name"      => $valueName
            ]
        ];
    }
}
