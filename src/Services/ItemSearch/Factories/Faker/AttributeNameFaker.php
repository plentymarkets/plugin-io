<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Services\ItemSearch\Contracts\VariationSearchResultFakerContract;

class AttributeNameFaker extends VariationSearchResultFakerContract
{
    public $isList = true;

    public function generate()
    {
        return [
            "attributeId"   => rand(1, 100),
            "lang"          => "de",
            "name"          => "Attribute name"
        ];
    }
}