<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class VariationFaker extends AbstractFaker
{
    public function fill($data)
    {
        $itemId             = $this->number();
        $variationId        = $this->number();
        $intervalQuantity   = $this->number(0, 10);
        $default = [
            "number"                                => $this->serial(),
            "externalId"                            => $this->serial(),
            "model"                                 => $this->serial(),
            "id"                                    => $variationId,
            "isMain"                                => $this->boolean(),
            "isActive"                              => $this->boolean(),
            "itemId"                                => $itemId,
            "mainVariationId"                       => $this->number(),
            "categoryVariationId"                   => $variationId,
            "marketVariationId"                     => $variationId,
            "clientVariationId"                     => $variationId,
            "salesPriceVariationId"                 => $variationId,
            "supplierVariationId"                   => $variationId,
            "warehouseVariationId"                  => $variationId,
            "position"                              => $this->number(),
            "priceCalculationId"                    => $this->number(),
            "estimatedAvailableAt"                  => $this->dateString(),
            "createdAt"                             => $this->dateString(),
            "updatedAt"                             => $this->dateString(),
            "relatedUpdatedAt"                      => $this->dateString(),
            "availabilityUpdatedAt"                 => $this->dateString(),
            "purchasePrice"                         => $this->float(),
            "picking"                               => $this->rand(["single_picking", "no_single_picking", "exclude_from_picklist"]),
            "stockLimitation"                       => 1,
            "isVisibleIfNetStockIsPositive"         => $this->boolean(),
            "isInvisibleIfNetStockIsNotPositive"    => $this->boolean(),
            "isAvailableIfNetStockIsPositive"       => $this->boolean(),
            "isUnavailableIfNetStockIsNotPositive"  => $this->boolean(),
            "isVisibleInListIfNetStockIsPositive"   => $this->boolean(),
            "isInvisibleInListIfNetStockIsNotPositive"  => $this->boolean(),
            "mainWarehouseId"                       => $this->number(),
            "maximumOrderQuantity"                  => $this->number($intervalQuantity * 10),
            "minimumOrderQuantity"                  => 0,
            "intervalOrderQuantity"                 => $intervalQuantity,
            "availableUntil"                        => $this->dateString(),
            "releasedAt"                            => $this->dateString(),
            "weightG"                               => $this->number(0, 1000),
            "weightNetG"                            => $this->number(0, 1000),
            "widthMM"                               => $this->number(0, 1000),
            "lengthMM"                              => $this->number(0, 1000),
            "heightMM"                              => $this->number(0, 1000),
            "extraShippingCharge1"                  => $this->float(),
            "extraShippingCharge2"                  => $this->float(),
            "palletTypeId"                          => 8,
            "packingUnits"                          => 4,
            "unitsContained"                        => 1,
            "packingUnitTypeId"                     => 4,
            "transportationCosts"                   => $this->float(),
            "storageCosts"                          => $this->float(),
            "customs"                               => $this->float(),
            "operatingCosts"                        => $this->float(),
            "vatId"                                 => 8,
            "bundleType"                            => $this->rand([null, "bundle", "bundle_item"]),
            "automaticClientVisibility"             => $this->number(0, 3),
            "automaticListVisibility"               => $this->number(0, 3),
            "isHiddenInCategoryList"                => $this->boolean(),
            "availability"                          => $this->makeAvailability(),
            "customsTariffNumber"                   => $this->serial()
        ];

        $this->merge($data, $default);
        return $data;
    }

    public function makeAvailability()
    {
        $availabilityId = $this->number(1, 10);
        return [
            "id"            => $availabilityId,
            "icon"          => $this->image(50, 50),
            "averageDays"   => $this->number(1, 50),
            "names"         => [
                "availabilityId" => $availabilityId,
                "lang"           => $this->lang,
                "name"           => $this->trans("IO::Faker.availabilityName")
            ]
        ];
    }
}