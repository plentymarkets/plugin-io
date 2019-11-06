<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class FacetFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $default = [
            $this->makeFacet()
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeFacet()
    {
        return [
            'id' => $this->number(),
            'name' => $this->trans("IO::Faker.facetName"),
            'position' => 0,
            'values' => $this->makeValues(),
            "type" => "dynamic"
        ];
    }

    private function makeValues()
    {
        $result = [];

        for ($i = 1; $i <= $this->number(3, 10); $i++)
        {
            $result[] = [
                'id' => $i,
                'name' => $this->trans("IO::Faker.facetValueName"),
                'count' => $this->number(1, 10),
            ];
        }

        return $result;
    }
}
