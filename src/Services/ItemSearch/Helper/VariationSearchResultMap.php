<?php

namespace IO\Services\ItemSearch\Helper;

use IO\Services\ItemSearch\Factories\Faker\AttributeNameFaker;

class VariationSearchResultMap
{
    const RESULT_FIELDS = [
        "attributes.attribute.names" => AttributeNameFaker::class,
    ];
}