<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class SalesPriceFaker extends AbstractFaker
{
    public $isList = true;

    public function fill($data)
    {
        $priceId = $this->number();
        $priceName = $this->trans("IO::Faker.salesPriceName");
        $default = [
            "id"                    => $priceId,
            "position"              => $this->number(0, 100),
            "minimumOrderQuantity"  => $this->number(0, 100),
            "type"                  => $this->rand(['default','rrp','specialOffer','set','subscription']),
            "isCustomerPrice"       => $this->boolean(),
            "isDisplayedByDefault"  => $this->boolean(),
            "isLiveConversion"      => $this->boolean(),
            "createdAt"             => $this->dateString(),
            "updatedAt"             => $this->dateString(),
            "price"                 => $this->float(),
            "settings"              => [
                "currencies"        => "Dr. Vincent Raynor Sr.", //TODO
                "countries"         => 2,
                "customerClasses"   => 6,
                "clients"           => 5,
                "referrers"         => 0.84
            ],
            "names"                 => [
                [
                    "priceId"       => $priceId,
                    "lang"          => $this->lang,
                    "nameInternal"  => $priceName,
                    "nameExternal"  => $priceName,
                    "updatedAt"     => $this->dateString(),
                    "createdAt"     => $this->dateString()
                ]
            ]
        ];

        $this->merge($data, $default);
        return $data;
    }
}