<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Helper\ArrayHelper;

class ItemFaker extends AbstractFaker
{
    public function fill($data)
    {
        $itemId = $this->number();
        $producingCountry = $this->country();
        $manufacturerId = $this->number();
        $default = [
            "id"                    => $itemId,
            "position"              => $this->number(0, 100),
            "manufacturerId"        => $manufacturerId,
            "stockType"             => $this->number(),
            "amazonFedas"           => $this->word(),
            "updatedAt"             => $this->dateString(),
            "createdAt"             => $this->dateString(),
            "customsTariffNumber"   => $this->serial(),
            "producingCountryId"    => $producingCountry->id,
            "revenueAccount"        => 6,
            "couponRestriction"     => 6,
            "ageRestriction"        => 7,
            "amazonProductType"     => $this->number(),
            "ebayPresetId"          => $this->number(),
            "ebayCategory"          => $this->number(),
            "ebayCategory2"         => $this->number(),
            "amazonFbaPlatform"     => $this->number(),
            "feedback"              => $this->number(0, 5),
            "feedbackDecimal"       => $this->float(0, 5),
            "feedbackCount"         => $this->number(),
            "isSubscribable"        => $this->boolean(),
            "rakutenCategoryId"     => $this->number(),
            "isShippableByAmazon"   => $this->boolean(),
            "ownerId"               => $this->boolean(),
            "type"                  => $this->rand(['default', 'set', 'multiPack']),
            "condition"          => [
                "id"    => $this->number(0,4),
                "names" => [
                    [
                        "lang" => $this->lang,
                        "name" => $this->trans("IO::Faker.conditionApiName")
                    ]
                ]
            ],
            "conditionApi"          => [
                "id"    => $this->number(),
                "names" => [
                    [
                        "lang" => $this->lang,
                        "name" => $this->trans("IO::Faker.conditionApiName")
                    ]
                ]
            ],
            "storeSpecial"          => [
                "id"    => $this->number(),
                "names" => [
                    [
                        "lang" => $this->lang,
                        "name" => $this->trans("IO::Faker.storeSpecial")
                    ]
                ]
            ],
            "manufacturer"          => [
                "id"            => $manufacturerId,
                "name"          => $this->trans("IO::Faker.manufacturerName"),
                "icon"          => $this->image(),
                "externalName"  => $this->word(),
                "position"      => $this->number(0, 100)
            ],
            "producingCountry"      => [
                "id"        => $producingCountry->id,
                "name"      => $producingCountry->name,
                "isoCode2"  => $producingCountry->isoCode2,
                "isoCode3"  => $producingCountry->isoCode3,
                "names"     => ArrayHelper::toArray($producingCountry->names)
            ],
            "flags"                 => []
        ];
        $this->merge($data, $default);
        return $data;
    }
}