<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class TextFaker extends AbstractFaker
{
    public function fill($data)
    {
        $default = [
            "name1"             => $this->trans("IO::Faker.itemName"),
            "name2"             => $this->trans("IO::Faker.itemName"),
            "name3"             => $this->trans("IO::Faker.itemName"),
            "description"       => $this->text(50),
            "shortDescription"  => $this->text(10),
            "metaDescription"   => $this->text(0, 10),
            "technicalData"     => $this->text(),
            "urlPath"           => $this->url(),
            "keywords"          => $this->text(0, 5)
        ];

        $this->merge($data, $default);
        return $data;
    }
}