<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class FilterFaker extends AbstractFaker
{
    public function fill($data)
    {
        $default = [
            "search"                    => $this->makeSearchFilter(),
            "names"                     => $this->makeNamesFilter(),
            "facets"                    => $this->makeFacetFilter(),
            "hasImage"                  => $this->boolean(),
            "hasItemImage"              => $this->boolean(),
            "hasVariationImage"         => $this->boolean(),
            "hasAttribute"              => $this->boolean(),
            "hasBarcode"                => $this->boolean(),
            "hasCategory"               => $this->boolean(),
            "hasManufacturer"           => $this->boolean(),
            "hasClient"                 => $this->boolean(),
            "hasMarket"                 => $this->boolean(),
            "hasSalesPrice"             => $this->boolean(),
            "hasSKU"                    => $this->boolean(),
            "hasSupplier"               => $this->boolean(),
            "hasFacet"                  => $this->boolean(),
            "hasChildren"               => $this->boolean(),
            "hasActiveChildren"         => $this->boolean(),
            "hasVariationProperties"    => $this->boolean(),
            "isSalable"                 => $this->boolean(),
            "isSalableAndActive"        => $this->boolean(),
            "barcodes"                  => "",
            "marketIdentNumbers"        => [
                "asin" => $this->serial(),
                "epid" => $this->serial(),
                "upc"  => $this->serial(),
                "rsin" => $this->serial()
            ],
            "hasDescriptionInLanguage"  => $this->boolean(),
            "hasName1InLanguage"        => $this->boolean(),
            "hasName2InLanguage"        => $this->boolean(),
            "hasName3InLanguage"        => $this->boolean(),
            "hasAnyNameInLanguage"      => $this->boolean(),
            "hasAnyName"                => $this->boolean()
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeSearchFilter()
    {
        $filter = [];

        $filter[$this->esLang] = [
            "names"             => $this->trans("IO::Faker.itemName"),
            "name1"             => $this->trans("IO::Faker.itemName"),
            "name2"             => $this->trans("IO::Faker.itemName"),
            "name3"             => $this->trans("IO::Faker.itemName"),
            "shortDescription"  => $this->text(0, 10),
            "description"       => $this->text(10, 20),
            "technicalData"     => $this->text(0, 20),
            "keywords"          => $this->text(0, 5),
            "search"            => $this->text(0, 10),
            "facets"            => "",
            "categories"        => "",
            "propertyValues"    => "",
            "properties"        => "",
            "attributeValues"   => ""
        ];

        return $filter;
    }

    private function makeNamesFilter()
    {
        $filter = [];

        $filter[$this->esLang] = [
            "hasAny"    => $this->boolean(),
            "hasName1"  => $this->boolean(),
            "hasName2"  => $this->boolean(),
            "hasName3"  => $this->boolean()
        ];

        return $filter;
    }

    private function makeFacetFilter()
    {
        return [];
    }
}