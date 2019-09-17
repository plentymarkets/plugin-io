<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Application;
use Plenty\Plugin\Translation\Translator;

class BasketFaker extends AbstractFaker
{
    private $rawBasketItems = [];

    public function fill($data)
    {
        $orderId = $this->number(1, 10000);

        $default = [

        ];

        $this->merge($data, $default);
        return $data;
    }

    public function setRawBasketItems($rawBasketitems)
    {
        $this->rawBasketItems = $rawBasketitems;
    }
}
