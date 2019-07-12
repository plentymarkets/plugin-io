<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class SkuFaker extends AbstractFaker
{
    public $isList = true;
    
    public function fill($data)
    {
        $default = [
            "id"                    => $this->number(),
            "variationId"           => $this->global("variationId", $this->number()),
            "accountId"             => $this->number(),
            "marketId"              => $this->number(),
            "initialSku"            => $this->word(),
            "sku"                   => $this->word(),
            "parentSku"             => $this->word(),
            "status"                => $this->rand(['INACTIVE', 'SENT', 'ACTIVE']),
            "additionalInformation" => $this->text(0, 10),
            "isActive"              => $this->boolean(),
            "createdAt"             => $this->dateString(),
            "exportedAt"            => $this->dateString(),
            "stockUpdatedAt"        => $this->dateString(),
            "deletedAt"             => $this->dateString()
        ];

        $this->merge($data, $default);
        return $data;
    }
}