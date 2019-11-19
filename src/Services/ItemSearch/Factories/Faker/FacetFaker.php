<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class FacetFaker extends AbstractFaker
{
    public $facetTypes = [
        "availability" => [
            "name"      => "IO::Faker.facetNameAvailability",
            "valueName" => "IO::Faker.facetValueNameAvailability"
        ],
        "category" => [
            "name"      => "IO::Faker.facetNameCategory",
            "valueName" => "IO::Faker.facetValueNameCategory"
        ],
        "dynamic" => [
            "name"      => "IO::Faker.facetNameDynamic",
            "valueName" => "IO::Faker.facetValueNameDynamic"
        ],
        "price" => [
            "name"      => "IO::Faker.facetNamePrice",
            "valueName" => "IO::Faker.facetValueNamePrice"
        ]
    ];

    public function fill($data)
    {
        $default = [];

        foreach ($this->facetTypes as $type => $names)
        {
            $default[] = $this->makeFacet($type, $names);
        }

        $this->merge($data, $default);
        return $data;
    }

    private function makeFacet($type, $names)
    {
        return [
            'id' => $type,
            'name' => $this->trans($names["name"]),
            'position' => 0,
            'values' => $this->makeValues($names["valueName"]),
            "type" => $type
        ];
    }

    private function makeValues($valueName)
    {
        $result = [];

        for ($i = 1; $i <= $this->number(3, 10); $i++)
        {
            $result[] = [
                'id' => $i.'',
                'name' => $this->trans($valueName),
                'count' => $this->number(1, 10),
            ];
        }

        return $result;
    }
}
