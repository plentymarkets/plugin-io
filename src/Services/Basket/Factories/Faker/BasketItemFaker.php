<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Translation\Translator;

class BasketItemFaker extends AbstractFaker
{
    private $rawBasketItems = [];

    public function fill($data)
    {
        $default = [

        ];

        if(count($this->rawBasketItems))
        {
            $id = 100;
            foreach ($this->rawBasketItems as $rawBasketItem)
            {
                $basketItem = [];

                $basketItem['variation'] = $rawBasketItem;
                $basketItem['variationId'] = $rawBasketItem['id'];
                $basketItem['basketItemOrderParams'] = [];
                $basketItem['price'] = $rawBasketItem['prices']['default']['data']['basePrice'];
                $basketItem['quantity'] = 1;
                $basketItem['id'] = $id;
                $id += 1;

                $default[] = $basketItem;
            }
        }


        $this->merge($data, $default);
        return $data;
    }

    public function setRawBasketItems($rawBasketitems)
    {
        $this->rawBasketItems = $rawBasketitems;
    }
}
