<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class UnitFaker extends AbstractFaker
{
    public function fill($data)
    {
        $unitId  = $this->number();
        $default = [
            "id"                    => $unitId,
            "position"              => $this->number(),
            "unitOfMeasurement"     => $this->unit(),
            "isDecimalPlacesAllowed"=> $this->boolean(),
            "updatedAt"             => $this->dateString(),
            "createdAt"             => $this->dateString(),
            "content"               => $this->float(),
            "names"                 => [
                [
                    "unitId" => $unitId,
                    "lang"   => $this->lang,
                    "name"   => $this->trans("IO::Faker.unitName")
                ]
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }
}