<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class BarcodeFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $default = [
            "code"      => uniqid(),
            "id"        => $this->number(),
            "name"      => $this->trans("IO::Faker.barcodeName"),
            "type"      => $this->rand(['EAN_8', 'EAN_13', 'EAN_14' ,'EAN_128', 'ISBN', 'QR', 'CODE_128', 'UPC']),
            "referrers" => $this->float()
        ];

        $this->merge($data, $default);
        return $data;
    }
}