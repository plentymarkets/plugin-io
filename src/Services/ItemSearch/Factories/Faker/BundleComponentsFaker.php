<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class BundleComponentsFaker extends AbstractFaker
{
    public function fill($data)
    {
        $default = [];

        for($i=0; $i < $this->number(2, 6); $i++)
        {
            $default[$i] = [
                "quantity"  => $this->number(1, 10),
                "data"      => [
                    "item" => [
                        "id" => $this->number(),
                    ],
                    "texts" => [
                        "name1" => $this->trans("IO::Faker.itemName"),
                        "urlPath" => $this->url()
                    ],
                    "variation" => [
                        "id" => $this->number(),
                    ],
                ]
            ];
        }

        $this->merge($data, $default);
        return $data; 
    }
}