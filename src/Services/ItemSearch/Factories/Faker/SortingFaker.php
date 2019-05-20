<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class SortingFaker extends AbstractFaker
{
    public function fill($data)
    {
        $price1 = $this->float();
        $price2 = $this->float();
        $manufacturerName = $this->trans("IO::Faker.manufacturerName");
        $default = [
            "price" => [
                "min" => min($price1, $price2),
                "max" => max($price1, $price2),
                "avg" => ($price1 + $price2) / 2
            ],
            "priceByClientDynamic" => [
                "min" => min($price1, $price2),
                "max" => max($price1, $price2),
                "avg" => ($price1 + $price2) / 2
            ],
            "manufacturer" => [
                "name"          => $manufacturerName,
                "nameExternal"  => $manufacturerName
            ]
        ];

        $default[$this->esLang] = [
            "name1" => $this->trans("IO::Faker.itemName"),
            "name2" => $this->trans("IO::Faker.itemName"),
            "name3" => $this->trans("IO::Faker.itemName"),
        ];

        $this->merge($data, $default);
        return $data;
    }
}