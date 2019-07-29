<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class PropertyFMD extends FieldMapDefinition
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
            VariationBaseAttribute::CHARACTERISTIC
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'properties';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $variationPropertyValues = $decoratedVariation->base->with()->characteristics;

        $data = [];
        foreach ($variationPropertyValues AS $variationPropertyValue) {
            $data[] = $variationPropertyValue;
        }

        $content['properties'] = $data;

        return $content;
    }
}
