<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class StockFaker extends AbstractFaker
{
    public function fill($data)
    {
        $default = [
            "net" => $this->float()
        ];

        $this->merge($data, $default);
        return $data;
    }
}