<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;

class BasketFaker extends AbstractFaker
{
    private $rawBasketItems = [];

    /**
     * @param $data
     * @return mixed
     */
    public function fill($data)
    {
        $orderId = $this->number(1, 10000);

        $default = [

        ];

        $this->merge($data, $default);
        return $data;
    }

    /**
     * @param $rawBasketitems
     */
    public function setRawBasketItems($rawBasketitems)
    {
        $this->rawBasketItems = $rawBasketitems;
    }

    private function getTotals()
    {

    }
}
