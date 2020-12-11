<?php

namespace IO\Services\Order\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use Plenty\Modules\Webshop\Helpers\NumberFormatter;

class TotalsFaker extends AbstractFaker
{
    public function fill($data)
    {
        $vatRate       = 19;
        $itemSumNet    = $this->number(10, 5000);
        $itemSumGross  = $itemSumNet + ($itemSumNet * $vatRate) / 100;
        $couponValue   = $this->number(1, $itemSumNet / 10);
        $vatValue      = $itemSumGross - $itemSumNet;
        $rebateNet     = $this->number(1, $itemSumNet / 10);
        $rebateGross   = $rebateNet + ($rebateNet * $vatRate) / 100;
        $shippingNet   = $this->number(1, $itemSumNet / 50);
        $shippingGross = $shippingNet + ($shippingNet * $vatRate) / 100;

        /** @var NumberFormatter $numberFormatter */
        $numberFormatter = pluginApp(NumberFormatter::class);

        $default = [
            'itemSumGross'       => $itemSumGross,
            'itemSumNet'         => $itemSumNet,
            'itemSumRebateGross' => $rebateGross,
            'itemSumRebateNet'   => $rebateNet,
            'shippingGross'      => $shippingGross,
            'shippingNet'        => $shippingNet,
            'couponValue'        => $couponValue,
            'openAmount'         => 0,
            'couponType'         => '',
            'couponCode'         => $this->word(),
            'totalGross'         => $itemSumGross,
            'totalNet'           => $itemSumNet,
            'currency'           => 'EUR',
            'isNet'              => false,
            'vats' => [
                [
                    'rate'  => $vatRate,
                    'value' => $vatValue
                ]
            ],
            'additionalCosts' => [
                [
                    'id' => $this->number(1, 100),
                    'quantity' => 3,
                    'name' => $this->text(1, 3),
                    'price' => $this->number(1, 5),
                    'currency' => 'EUR',
                    'formattedTotalPrice' => $numberFormatter->formatMonetary($this->number(1, 5), 'EUR')
                ]
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }


}
