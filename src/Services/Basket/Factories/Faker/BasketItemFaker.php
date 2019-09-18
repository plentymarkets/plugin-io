<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Translation\Translator;

class BasketItemFaker extends AbstractFaker
{
    private $rawBasketItems = [];

    public function fill($default)
    {
        $data = [];

        if(count($this->rawBasketItems))
        {
            $id = 100;
            foreach ($this->rawBasketItems as $rawBasketItem)
            {
                $itemData = $rawBasketItem['itemData'];
                $quantity = $rawBasketItem['quantity'];

                $basketItem = [];
                $basketItem['variation'] = $itemData;
                $basketItem['variationId'] = $itemData['id'];
                $basketItem['basketItemOrderParams'] = [];
                $basketItem['price'] = $itemData['data']['prices']['default']['data']['basePrice'];
                $basketItem['quantity'] = $quantity;
                $basketItem['id'] = $id;
                $id += 1;

                $data[] = $basketItem;
            }
        }


        $this->merge($default, $data);
        return $default;
    }

    public function setRawBasketItems($rawBasketitems)
    {
        $this->rawBasketItems = $rawBasketitems;
    }
}
