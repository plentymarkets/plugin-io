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

        $content['variation'] = $docVariation;
        return $content;
    }
}
