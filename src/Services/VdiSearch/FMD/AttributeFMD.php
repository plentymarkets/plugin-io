<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;


class AttributeFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationAttributeValueAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationAttributeValueAttribute::VALUE,
            VariationAttributeValueAttribute::ATTRIBUTE
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'attributes';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $attributeValues = $decoratedVariation->attributeValues;

        $data = [];
        foreach ($attributeValues as $attributeValue) {
            $attribute = $attributeValue->with()->attribute;
            $attribute = $this::map($attribute, 'attributeNames', 'names');

            $value = $attributeValue->with()->attributeValue;
            $value = $this::map($value, 'valueNames', 'names');

            $entry = [
                'attributeId' => $attribute->id,
                'valueId' => $value->id,
                'isLinkableToImage' => $attribute->isLinkableToImage,
                'attribute' => $attribute->toArray(),
                'value' => $value->toArray()
            ];

            $data[] = $entry;
        }

        $content['attributes'] = $data;


        return $content;
    }
}
