<?php

namespace IO\Services\ItemSearch\Factories\Faker;


class AttributeFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $attributeId  = $this->uniqueNumber();
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
        return [
            "id"                            => $attributeId,
            "backendName"                   => "Ryleigh Wilderman",
            "position"                      => $this->number(),
            "isSurchargePercental"          => $this->boolean(),
            "isLinkableToImage"             => $this->boolean(),
            "amazonAttribute"               => "Dr. Emilia Tremblay",
            "fruugoAttribute"               => "Ms. Joannie Halvorson MD",
            "pixmaniaAttribute"             => $this->number(),
            "ottoAttribute"                 => "Dr. Abbigail Barrows V",
            "googleShoppingAttribute"       => "Alexander McLaughlin",
            "neckermannAtEpAttribute"       => $this->number(),
            "typeOfSelectionInOnlineStore"  => "Jeffery Purdy",
            "laRedouteAttribute"            => $this->number(),
            "isGroupable"                   => $this->boolean(),
            "updatedAt"                     => $this->dateString("Y-m-d"),
            "names"                         => [
                [
                    "attributeId"   => $attributeId,
                    "lang"          => "Jace Haag",
                    "name"          => "Fletcher Runolfsdottir"
                ],
                [
                    "attributeId"   => $attributeId,
                    "lang"          => "Genesis Nolan",
                    "name"          => "Marguerite Tremblay"
                ]
            ]
        ];
    }

    private function makeValue($attributeId, $valueId)
    {
        return [
            "id"                    => $valueId,
            "attributeId"           => $attributeId,
            "backendName"           => "Mrs. Rosanna Wyman",
            "position"              => $this->number(),
            "image"                 => "Lupe Considine Jr.",
            "comment"               => "Jaida Runolfsdottir V",
            "amazonValue"           => "Prof. Jessika Lueilwitz I",
            "ottoValue"             => "Dr. Dashawn Baumbach",
            "neckermannAtEpValue"   => "Nathanael Hauck",
            "laRedouteValue"        => "Emmitt Mayert I",
            "tracdelightValue"      => "Margarett Corwin",
            "percentageDistribution"=> 4312261.8118745,
            "updatedAt"             => $this->dateString("Y-m-d"),
            "names"                 => [
                [
                    "lang"      => "Linwood Willms",
                    "valueId"   => $valueId,
                    "name"      => "Renee Windler II"
                ]
            ]
        ];
    }
}