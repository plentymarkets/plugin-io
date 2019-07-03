<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class IdsFaker extends AbstractFaker
{
    public function fill($data)
    {
        $default = [
            "itemId"                => $this->global("itemId", $this->number()),
            "itemAttributeValue"    => $this->global("itemId", $this->number()),
            "clients"               => 3, // TODO
            "suppliers"             => 4,
            "attributes"            => 6,
            "attributeValues"       => 3,
            "facets"                => 2,
            "facetValues"           => 0,
            "barcodes"              => 2,
            "salesPrices"           => 3,
            "tags"                  => 7,
            "variationProperties"   => 6,
            "markets"               => "Karli Padberg",
            "categories"            => [
                "all"       => 3,
                "branches"  => 3
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }
}