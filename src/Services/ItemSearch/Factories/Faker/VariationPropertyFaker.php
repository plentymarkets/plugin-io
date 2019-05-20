<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class VariationPropertyFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $propertyId = $this->number();
        $default = [
            "id"        => $propertyId,
            "property"  => $this->makeProperty($propertyId),
            "values"    => [
                [
                    "lang"          => $this->lang,
                    "value"         => $this->word(),
                    "description"   => $this->text(0, 10)
                ]
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeProperty($propertyId)
    {
        return [
            "id"        => $propertyId,
            "position"  => $this->number(),
            "cast"      => $this->rand(['empty', 'int', 'float', 'selection', 'shortText', 'longText', 'date', 'file', 'contactType']),
            "options"   => [],
            "clients"   => 1,
            "referrer"  => $this->float(),
            "display"   => "",
            "groups"    => [
                $this->makePropertyGroup()
            ]
        ];
    }

    private function makePropertyGroup()
    {
        return [
            "id"        => $this->number(),
            "position"  => $this->number(),
            "names"     => [
                "lang"          => $this->lang,
                "name"          => $this->trans("IO::Faker.variationPropertyName"),
                "description"   => $this->text(0, 10)
            ]
        ];
    }
}