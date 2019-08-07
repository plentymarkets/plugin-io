<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class VariationFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationBaseAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationBaseAttribute::AVAILABILITY
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'variation';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $base = $decoratedVariation->base;

        $docVariation = collect($base->toArray())
            ->except('availability')
            ->toArray();

        $baseAvailability = $base->with()->availability;
        $docVariation['availability'] = null;

        if ($baseAvailability) {

            $availability = $baseAvailability->toArray();
            foreach($baseAvailability->names->toArray() as $name)
            {
                $availability['names'][] = $name;
            }
            $docVariation['availability'] = $availability;
        }


        $docVariation['id'] = (int)$decoratedVariation->id;

        $docVariation['salesRank'] = $base->position;
$mainVariationId = $base->mainVariationId ?? $decoratedVariation->id;

        //$docVariation['unitCombinationId'] = $decoratedVariation->unit->unitCombinationId;

        $docVariation['categoryVariationId'] = $base->categoriesInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['marketVariationId'] = $base->referrerInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['clientVariationId'] = $base->clientsInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['salesPriceVariationId'] = $base->salesPricesInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['supplierVariationId'] = $base->supplierInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['warehouseVariationId'] = $base->warehousesInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['propertyVariationId'] = $base->propertiesInherited ? $mainVariationId : $decoratedVariation->id;
        $docVariation['tagVariationId'] = $base->tagsInherited ? $mainVariationId : $decoratedVariation->id;

        $docVariation['updatedAt'] = $decoratedVariation->timestamps->base;
        $docVariation['relatedUpdatedAt'] = $decoratedVariation->timestamps->related;
        $docVariation['createdAt'] = $decoratedVariation->timestamps->createdAt;
        $docVariation['availabilityUpdatedAt'] = $decoratedVariation->timestamps->availability;
        $content['variation'] = $docVariation;
        return $content;
    }
}
