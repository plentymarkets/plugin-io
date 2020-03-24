<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Extensions\Filters\NumberFormatFilter;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;

class PriceFaker extends AbstractFaker
{
    private $currency;

    private $showNetPrice = false;

    /** @var NumberFormatFilter */
    private $numberFormatFilter;

    public function fill($data)
    {
        /** @var  CheckoutRepositoryContract $checkoutRepository */
        $checkoutRepository = pluginApp(CheckoutRepositoryContract::class);

        $this->currency             = $checkoutRepository->getCurrency() ?? 'EUR';
        $this->numberFormatFilter   = pluginApp(NumberFormatFilter::class);
        $this->showNetPrice         = $this->boolean();
        $defaultPrice               = $this->makePrice(0);
        $default = [
            'default'           => $defaultPrice,
            'rrp'               => $this->makePrice(0),
            'specialOffer'      => $this->boolean() ? $this->makePrice(0) : null,
            'graduatedPrices'   => $this->makeGraduatedPrices($defaultPrice),
            'set'               => $this->makePrice(0)
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeGraduatedPrices($defaultPrice)
    {
        $price1 = $this->makePrice(rand(2, 5), $defaultPrice['unitPrice']['value']);
        $price2 = $this->makePrice(rand(5, 25), $price1['unitPrice']['value']);
        $price3 = $this->makePrice(rand(25, 100), $price2['unitPrice']['value']);

        return [$price3, $price2, $price1];
    }

    private function makePrice($minimumOrderQuantity, $maxPriceValue = 2000)
    {
        $price = $this->makePriceRaw($minimumOrderQuantity, $maxPriceValue);
        $unitPrice = $this->showNetPrice ? $price['unitPriceNet'] : $price['unitPrice'];
        return [
            'price'                 => [
                'value'     => $this->showNetPrice ? $price['priceNet'] : $price['price'],
                'formatted' => $this->numberFormatFilter->formatMonetary( $this->showNetPrice ? $price['priceNet'] : $price['price'], $price['currency'] )
            ],
            'unitPrice'             => [
                'value'     => $unitPrice,
                'formatted' => $this->numberFormatFilter->formatMonetary( $unitPrice, $price['currency'] )
            ],
            'basePrice'             => $this->numberFormatFilter->formatMonetary( $unitPrice, $price['currency'] ),
            'baseLot'               => $this->number(),
            'baseUnit'              => $this->unit(),
            'baseSinglePrice'       => $this->float(),

            'minimumOrderQuantity'  => (float) $price['minimumOrderQuantity'],
            'contactClassDiscount'  => [
                'percent'   => $price['customerClassDiscountPercent'],
                'amount'    => $this->showNetPrice ? $price['customerClassDiscountNet'] : $price['customerClassDiscount']
            ],
            'categoryDiscount'      => [
                'percent'   => $price['categoryDiscountPercent'],
                'amount'    => $this->showNetPrice ? $price['categoryDiscountNet'] : $price['categoryDiscount']
            ],
            'currency'              => $price['currency'],
            'vat'                   => [
                'id'        => $price['vatId'],
                'value'     => $price['vatValue']
            ],
            'isNet'                 => $this->showNetPrice,
            'data'                  => $price
        ];
    }

    private function makePriceRaw($minimumOrderQuantity, $maxPriceValue = 2000)
    {
        $valueNet = $this->float(0, $maxPriceValue);
        $vatValue = $this->number(5, 20);
        $valueGross = $valueNet * ( 1 + ($vatValue / 100) );

        return [
            "salesPriceId"  => $this->number(),
            "price"         => $valueGross,
            "priceNet"      => $valueNet,
            "basePrice"     => $valueGross,
            "basePriceNet"  => $valueNet,
            "unitPrice"     => $valueGross,
            "unitPriceNet"  => $valueNet,
            "customerClassDiscountPercent"=> 0,
            "customerClassDiscount"     => 0,
            "customerClassDiscountNet"  => 0,
            "categoryDiscountPercent"   => 0,
            "categoryDiscount"          => 0,
            "categoryDiscountNet"       => 0,
            "vatId"                     => 0,
            "vatValue"                  => $vatValue,
            "currency"                  => $this->currency,
            "interval"                  => "none",
            "conversionFactor"          => $this->float(0, 10),
            "minimumOrderQuantity"      => $minimumOrderQuantity,
            "updatedAt"                 => $this->dateString()
        ];
    }
}
