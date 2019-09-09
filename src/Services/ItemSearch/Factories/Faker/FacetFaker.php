<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class FacetFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $default = [
            "facet" => $this->makeFacet(),
            "value" => $this->makeValue()
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeFacet()
    {
        return [
            "id"    => $this->number(),
            "names" => [
                [
                    "lang" => $this->lang,
                    "name" => $this->trans("IO::Faker.facetName")
                ]
            ]
        ];
    }

    private function makeValue()
    {
        return [
            "id"    => $this->number(),
            "names" => [
                [
                    "lang" => $this->lang,
                    "name" => $this->trans("IO::Faker.facetValueName")
                ]
            ]
        ];
    }
}