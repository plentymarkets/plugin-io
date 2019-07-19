<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class TagFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $default = [
            "id"    => $this->number(),
            "names" => [
                [
                    "lang" => $this->lang,
                    "name" => $this->trans("IO::Faker.tagName")
                ]
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }
}