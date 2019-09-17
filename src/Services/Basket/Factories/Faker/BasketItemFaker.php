<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Translation\Translator;

class BasketItemFaker extends AbstractFaker
{
    public function fill($data)
    {
        $orderId = $this->number(1, 10000);

        $default = [

        ];

        $this->merge($data, $default);
        return $data;
    }
}
