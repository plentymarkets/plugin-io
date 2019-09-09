<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class CrossSellingFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $default = [
            "itemId"        => $this->number(),
            "relationship"  => $this->rand(['Variation', 'DisplaySet', 'Collection', 'Accessory', 'Customized', 'Part', 'Complements', 'Piece', 'Necessary', 'ReplacementPart', 'Similar', 'Episode', 'Season', 'Bundle', 'Component']),
            "isDynamic"     => $this->boolean()
        ];

        $this->merge($data, $default);
        return $data;
    }
}