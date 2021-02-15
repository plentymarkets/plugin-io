<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;

/**
 * Class BasketItemFaker
 * Factory class to generate random basket items or fill existing data to be used in the ShopBuilder preview.
 *
 * @package IO\Services\Basket\Factories\Faker
 */
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

        return $data;
    }

    /**
     * Set raw data to be considered while generating the list of random basket items.
     *
     * @param array $rawBasketItems
     */
    public function setRawBasketItems($rawBasketItems)
    {
        $this->rawBasketItems = $rawBasketItems;
    }
}
