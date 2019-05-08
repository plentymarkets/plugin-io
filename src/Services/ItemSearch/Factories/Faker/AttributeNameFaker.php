<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Services\ItemSearch\Helper\VariationSearchResultAbstractFaker;

class AttributeNameFaker extends VariationSearchResultAbstractFaker
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